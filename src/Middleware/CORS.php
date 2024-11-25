<?php

namespace ACAT\Slim\Middleware;

use Slim\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
final class CORS implements MiddlewareInterface
{

    /**
     * @param   ServerRequestInterface   $request
     * @param   RequestHandlerInterface  $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->createCORSHeader(new Response())->withStatus(204);
        }

        $response = $handler->handle($request);

        return $this->createCORSHeader($response);
    }

    /**
     * @param   ResponseInterface  $response
     *
     * @return ResponseInterface
     */
    private function createCORSHeader(ResponseInterface $response) : ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

}