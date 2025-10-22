<?php

namespace ACAT\Slim\Middleware;

use Closure;
use ACAT\JWT\TokenInterface;

/**
 *
 */
class TokenInjector implements TokenInjectorInterface
{

    /**
     * @var Closure
     */
    private Closure $setterCallback;

    /**
     * @param   Closure  $setterCallback
     */
    public function __construct(Closure $setterCallback) {
        $this->setterCallback = $setterCallback;
    }

    /**
     * @return void
     *
     * @param   TokenInterface  $token
     */
    public function injectToken(TokenInterface $token) : void
    {
        ($this->setterCallback)($token);
    }

}