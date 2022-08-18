<?php

namespace ACAT\Slim\Handlers;

use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Handlers\ErrorHandler;


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
