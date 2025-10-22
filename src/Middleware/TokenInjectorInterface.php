<?php

namespace ACAT\Slim\Middleware;

use ACAT\JWT\TokenInterface;

/**
 *
 */
interface TokenInjectorInterface
{

    /**
     * @return void
     *
     * @param   TokenInterface  $token
     */
    public function injectToken(TokenInterface $token): void;

}