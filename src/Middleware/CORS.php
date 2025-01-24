<?php

namespace ACAT\Slim\Middleware;

use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
final class CORS implements MiddlewareInterface {

	/**
	 * @var array
	 */
	private array $allowedOrigins;

	/**
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	/**
	 * @var ResponseFactory
	 */
	private ResponseFactory $responseFactory;

	/**
	 * @param   LoggerInterface  $logger
	 * @param   array            $allowedOrigins
	 * @param   ResponseFactory  $responseFactory
	 */
	public function __construct(LoggerInterface $logger, array $allowedOrigins, ResponseFactory $responseFactory) {
		$this->logger = $logger;
		$this->allowedOrigins = $allowedOrigins;
		$this->responseFactory = $responseFactory;
	}

	/**
	 * @param   ServerRequestInterface   $request
	 * @param   RequestHandlerInterface  $handler
	 *
	 * @return ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface {

		$origin = $request->getHeaderLine('Origin');

		if ( ! $origin || ! in_array($origin, $this->allowedOrigins)) {
			$this->logger->warning('origin ' . ($origin ?: 'unknown') . ' not set or allowed');
			return $this->responseFactory->createResponse(403);
		}

		if ($request->getMethod() === 'OPTIONS') {
			return $this->createCORSHeader($this->responseFactory->createResponse(204), $origin);
		}

		$response = $handler->handle($request);
		return $this->createCORSHeader($response, $origin);

	}

	/**
	 * @param   ResponseInterface  $response
	 * @param   string             $origin
	 *
	 * @return ResponseInterface
	 */
	private function createCORSHeader(ResponseInterface $response, string $origin) : ResponseInterface {
		return $response
			->withHeader('Access-Control-Allow-Origin', $origin)
			->withHeader('Access-Control-Allow-Credentials', 'true')
			->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE, PATCH')
			->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

}