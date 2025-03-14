<?php


namespace ACAT\Slim\Middleware;

use ACAT\JWT\AcatToken;
use ACAT\JWT\Exception\TokenException;
use ACAT\Slim\Exception\AuthorizeException;
use Nowakowskir\JWT\Exceptions\IntegrityViolationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 *
 */
final class Authorize implements MiddlewareInterface {

    /**
     * @var array
     */
    private array $acl;

    /**
     * @var string
     */
    private string $scope;

    /**
     * @var AcatToken
     */
    private AcatToken $token;

    /**
     * @var string|null
     */
    private ?string $cookieName;

    /**
     * @var string
     */
    private string $tokenName = "Bearer";

    /**
     * @var string
     */
    private string $header = "Authorization";

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param   AcatToken        $token
     * @param   string           $scope
     * @param   array            $acl
     * @param   LoggerInterface  $logger
     * @param   string|null      $cookieName
     */
    public function __construct(AcatToken $token, string $scope, array $acl, LoggerInterface $logger, ?string $cookieName = null) {
        $this->acl = $acl;
        $this->scope = $scope;
        $this->token = $token;
        $this->logger = $logger;
        $this->cookieName = $cookieName;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {

	    if ($request->getMethod() !== 'OPTIONS') {
		    try {
			    $this->validateRequest($request);
		    }
		    catch (AuthorizeException | TokenException $e) {

			    $this->logger->critical($e->getMessage());

			    return (new ResponseFactory())->createResponse(401);
		    }
	    }

        return $handler->handle($request);

    }

    /**
     * @param ServerRequestInterface $request
     * @throws AuthorizeException
     * @throws TokenException
     */
    public function validateRequest(ServerRequestInterface $request) : void {

        if ($this->cookieName) {
            $jwt = $this->getTokenStringFromCookie($request);
        }
        else {
            $jwt = $this->getTokenStringFromHeader($request);
        }

        $this->token->decode($jwt);

        if (!in_array($this->scope, $this->token->getScopes())) {
            throw new AuthorizeException("scope doesn't match");
        }

        if (!array_key_exists($this->token->getIssuer(), $this->acl)) {
            throw new AuthorizeException("issuer rejected");
        }

        if (empty($this->acl[$this->token->getIssuer()])) {
            throw new AuthorizeException('invalid acl configuration ' . $this->token->getIssuer() . ' has no public key');
        }

        $publicKeyUrl = $this->acl[$this->token->getIssuer()];

        if (!$publicKeyUrl) {
            throw new AuthorizeException("issuer rejected. No public key found");
        }

        $publicKey = file_get_contents($publicKeyUrl);

        if (!$publicKey) {
            throw new AuthorizeException('invalid public key from ' . $publicKeyUrl);
        }

        $this->token->validateToken($publicKey);
        $GLOBALS['token'] = $this->token;

        $this->logger->debug('granted access for ' . $this->token->getName());

    }

    /**
     * @throws AuthorizeException
     * @return string
     *
     * @param   ServerRequestInterface  $request
     */
    private function getTokenStringFromCookie(ServerRequestInterface $request) : string {

        if(empty($request->getCookieParams()[$this->cookieName])) {
            throw new AuthorizeException('Authorization cookie missing');
        }

        return $request->getCookieParams()[$this->cookieName];

    }

    /**
     * @throws AuthorizeException
     * @return string
     *
     * @param   ServerRequestInterface  $request
     */
    private function getTokenStringFromHeader(ServerRequestInterface $request) : string {

        if (!array_key_exists($this->header, $request->getHeaders()) || !$request->getHeader($this->header)) {
            throw new AuthorizeException('authorization failed. header is missing');
        }

        $authorizationString = $request->getHeader($this->header)[0];

        if (!str_contains($authorizationString, $this->tokenName)) {
            throw new AuthorizeException('invalid authorization header or token');
        }

        return trim(str_replace($this->tokenName,'', $authorizationString));

    }
}