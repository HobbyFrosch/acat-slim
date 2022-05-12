<?php

namespace ACAT\Slim\Exception;

use ACAT\Approval\Core\Exception\Throwable;
use Exception;
use JetBrains\PhpStorm\Pure;

/**
 *
 */
class RecordNotFoundException extends Exception {

    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    #[Pure]
    public function __construct(string $message = "", int $code = 404, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }


}