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
	 * @var bool
	 */
	private bool $strict;

	/**
	 * @var string[]
	 */
	private array $allowedHeaders = [
		'Content-Type',
		'Authorization'
	];

	/**
	 * @var string[]
	 */
	private array $allowedMethods = [
		'GET',
		'POST',
		'OPTIONS',
		'PUT',
		'DELETE',
		'PATCH'
	];

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
	 * @param   ResponseFactory  $responseFactory
	 * @param   array            $allowedOrigins
	 * @param   array            $allowedHeaders
	 * @param   array            $allowedMethods
	 * @param   bool             $strict
	 */
	public function __construct(LoggerInterface $logger, ResponseFactory $responseFactory, array $allowedOrigins, array $allowedHeaders = [], array $allowedMethods = [], bool $strict = true) {

		$this->strict = $strict;
		$this->logger = $logger;
		$this->allowedOrigins = $allowedOrigins;
		$this->responseFactory = $responseFactory;

		if ($allowedMethods) {
			$this->allowedMethods = $allowedMethods;
		}

		if ($allowedHeaders) {
			$this->allowedHeaders = $allowedHeaders;
		}

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
			if ($this->strict) {
				$this->logger->warning('origin '.($origin ?: 'unknown').' not set or allowed');
				return $this->responseFactory->createResponse(403);
			}
			else {
				$origin = '*';
			}
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

		if ($origin !== '*') {
			$response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
		}

		return $response
			->withHeader('Access-Control-Allow-Origin', $origin)
			->withHeader('Access-Control-Allow-Methods', implode(', ', $this->allowedMethods))
			->withHeader('Access-Control-Allow-Headers', implode(', ', $this->allowedHeaders));
    }

}