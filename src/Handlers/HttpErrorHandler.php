<?php

namespace ACAT\Slim\Handlers;

use ACAT\Slim\Actions\ActionError;
use ACAT\Slim\Actions\ActionPayload;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler;
use Slim\Psr7\Response;
use Throwable;

/**
 *
 */
class HttpErrorHandler extends ErrorHandler {

    /**
     * @inheritdoc
     */
    protected function respond(): ResponseInterface {

        if (!$this->exception instanceof  HttpNotFoundException) {
            $this->logger->critical($this->exception->getMessage());
        }

        return $this->responseFactory->createResponse($this->exception->getCode());

    }

}
