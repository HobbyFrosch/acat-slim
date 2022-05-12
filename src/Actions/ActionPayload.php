<?php

namespace ACAT\Slim\Actions;

use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;

/**
 * Class ActionPayload
 * @package ACAT\Payment\Application\Actions
 */
class ActionPayload implements JsonSerializable {

    /**
     * @var int
     */
    private int $statusCode;

    /**
     * @var mixed|null
     */
    private mixed $data;

    /**
     * @var ActionError|null
     */
    private ?ActionError $error;

	/**
	 * ActionPayload constructor.
	 * @param int $statusCode
	 * @param null $data
	 * @param ActionError|null $error
	 */
    public function __construct(int $statusCode = 200, $data = null, ?ActionError $error = null) {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->error = $error;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int {
        return $this->statusCode;
    }

    /**
     * @return mixed
     */
    public function getData() : mixed {
        return $this->data;
    }

    /**
     * @return ActionError|null
     */
    public function getError(): ?ActionError {
        return $this->error;
    }

    /**
     * @return array
     */

    #[ArrayShape(['statusCode' => "int", 'data' => "mixed|null", 'error' => "\ACAT\Slim\Actions\ActionError|null"])]
    public function jsonSerialize() : array {
        $payload = [
            'statusCode' => $this->statusCode,
        ];

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        } elseif ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        return $payload;
    }
}
