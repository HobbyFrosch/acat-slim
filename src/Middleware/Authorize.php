<?php


namespace ACAT\Slim\Middleware;

use ACAT\JWT\TokenDecoder;
use Psr\Log\LoggerInterface;
use ACAT\JWT\TokenInterface;
use ACAT\JWT\TokenAuthorizer;
use ACAT\JWT\Exception\TokenException;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use ACAT\Slim\Exception\AuthorizeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
final class Authorize implements MiddlewareInterface
{

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $realm;

    /**
     * @var string
     */
    private string $client;

    /**
     * @var string
     */
    private string $allowedIssuer;

    /**
     * @var string|null
     */
    private ?string $requiredRole;

    /**
     *
     */
    private const string HTTP_OPTIONS = 'OPTIONS';

    /**
     * @var string
     */
    private const string TOKEN_TYPE = "Bearer";


    /**
     * @var string
     */
    private const string HEADER = "Authorization";

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param   LoggerInterface  $logger
     * @param   string           $url
     * @param   string           $realm
     * @param   string           $client
     * @param   string           $allowedIssuer
     * @param   string|null      $requiredRoles
     */
    public function __construct(LoggerInterface $logger, string $url, string $realm, string $client, string $allowedIssuer, ?string $requiredRoles = null) {
        $this->url = $url;
        $this->realm = $realm;
        $this->logger = $logger;
        $this->client = $client;
        $this->requiredRole = $requiredRoles;
        $this->allowedIssuer = $allowedIssuer;

    }

    /**
     * @return ResponseInterface
     *
     * @param   RequestHandlerInterface  $handler
     * @param   ServerRequestInterface   $request
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {

        if ($request->getMethod() !== self::HTTP_OPTIONS) {
            try {
                $token = $this->validateRequest($request);
                $request = $request->withAttribute('token', $token);
            } catch (AuthorizeException | TokenException $e) {
                $this->logger->critical($e->getMessage());
                return new ResponseFactory()->createResponse(401);
            }
        }

        return $handler->handle($request);

    }

    /**
     * @throws AuthorizeException
     * @throws TokenException
     * @return TokenInterface
     *
     * @param   ServerRequestInterface  $request
     */
    public function validateRequest(ServerRequestInterface $request) : TokenInterface
    {

        $tokenAuthorizer = new TokenAuthorizer();
        $tokenDecoder = new TokenDecoder($this->url, $this->realm);

        $jwt = $this->getTokenStringFromHeader($request);

        $token = $tokenDecoder->decodeToken($jwt);

        if (!$tokenAuthorizer->authorize($token, $this->client, $this->allowedIssuer, $this->requiredRole)) {
            throw new AuthorizeException("Authorization failed: role '{$this->requiredRole}' not granted for resource '{$this->client}'");
        }

        $this->logger->debug('granted access for '.$token->getName());

        return $token;

    }

    /**
     * @throws AuthorizeException
     * @return string
     *
     * @param   ServerRequestInterface  $request
     */
    private function getTokenStringFromHeader(ServerRequestInterface $request) : string
    {

        if (!array_key_exists(self::HEADER, $request->getHeaders()) || !$request->getHeader(self::HEADER)) {
            throw new AuthorizeException('authorization failed. header is missing');
        }

        $authorizationString = $request->getHeaderLine(self::HEADER);

        if (!str_starts_with($authorizationString, self::TOKEN_TYPE.' ')) {
            throw new AuthorizeException('Invalid token type');
        }

        return trim(str_replace(self::TOKEN_TYPE, '', $authorizationString));

    }

}