<?php

namespace ACAT\Slim\Exception;

use Exception;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Factory\StreamFactory;

/**
 *
 */
class AuthorizeException extends Exception {

    /**
     * @return StreamInterface
     */
    public function getStreamBody() : StreamInterface {
        return (new StreamFactory)->createStream($this->getMessage());
    }

}