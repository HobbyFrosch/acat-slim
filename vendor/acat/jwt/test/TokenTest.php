<?php

namespace Tests;

use ACAT\JWT\Exception\TokenException;
use ACAT\JWT\AcatToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class TokenTest extends TestCase {

    /**
     * @return void
     */
    public function aTokenCanBeCreated(): void {

        $jwt = $this->aJWTCanBeCreated();

        $token = new AcatToken();
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @return string
     */
    public function aJWTCanBeCreated(): string {

        $jwt = JWT::encode($this->getPayload(), $this->getPrivateKey(), 'RS256');
        $this->assertNotEmpty($jwt);

        return $jwt;

    }

    /**
     * @test
     */
    public function aJWTCanBeDecoded(): void {

        $jwt = $this->aJWTCanBeCreated();
        $this->assertNotEmpty($jwt);

        $payload = (array)JWT::decode($jwt, new Key($this->getPublicKey(), 'RS256'));
        $this->assertIsArray($payload);

        $this->arrayHasKey('iss');
        $this->arrayHasKey('exp');
        $this->arrayHasKey('name');
        $this->arrayHasKey('email');
        $this->arrayHasKey('at_hah');
        $this->arrayHasKey('scope');
        $this->arrayHasKey('acat:id');

        $this->assertEquals('https://foo.de', $payload['iss']);
        $this->assertEquals('User', $payload['name']);
        $this->assertEquals('user@domain.tld', $payload['email']);
        $this->assertEquals('bild', $payload['at_hah']);
        $this->assertEquals('ms path write', $payload['scope']);
        $this->assertEquals('1', $payload['acat:id']);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aTokenWithoutIssuerThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $jwt = $this->aJWTCanBeCreated();

        unset($config['issuer']);

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aIssuerDontMatchThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $jwt = $this->aJWTCanBeCreated();

        $config['issuer'] = 'foo';

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aTokenWithoutNameThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $payload = $this->getPayload();

        unset($payload['name']);

        $jwt = JWT::encode($payload, $this->getPrivateKey(), 'RS256');

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aTokenWithoutMailThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $payload = $this->getPayload();

        unset($payload['email']);

        $jwt = JWT::encode($payload, $this->getPrivateKey(), 'RS256');

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aTokenWithoutAccessTokenThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $payload = $this->getPayload();

        unset($payload['at_hah']);

        $jwt = JWT::encode($payload, $this->getPrivateKey(),'RS256');

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aTokenWithoutUserIdThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $payload = $this->getPayload();

        unset($payload['acat:id']);

        $jwt = JWT::encode($payload, $this->getPrivateKey(), 'RS256');

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     * @throws TokenException
     */
    public function aTokenWithoutScopeThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $payload = $this->getPayload();

        unset($payload['scope']);

        $jwt = JWT::encode($payload, $this->getPrivateKey(), 'RS256');

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @test
     */
    public function aScopeThatDoesntMatchThrowsException() : void {

        $this->expectException(TokenException::class);

        $config = $this->getConfig();
        $payload = $this->getPayload();

        $payload['scope'] = "foo";

        $jwt = JWT::encode($payload, $this->getPrivateKey(), 'RS256');

        $token = new AcatToken($config, 'file://' . __DIR__ . '/resources/public.key');
        $this->assertInstanceOf(AcatToken::class, $token);

        $token->createToken($jwt);

    }

    /**
     * @return string[]
     */
    private function getConfig(): array {
        return [
            "issuer" => "https://foo.de",
            "scope"  => "ms path write",
        ];
    }

    /**
     * @return array
     */
    private function getPayload(): array {
        return [
            "iss"     => "https://foo.de",
            "exp"     => time() + 1000000000000,
            "name"    => "User",
            "email"   => "user@domain.tld",
            "at_hah"  => "bild",
            "scope"   => "ms path write",
            "acat:id" => 1,
        ];
    }

    /**
     * @return string
     */
    private function getPrivateKey(): string {
        return file_get_contents('file://' . __DIR__ . '/resources/private.key');
    }

    /**
     * @return string
     */
    private function getPublicKey(): string {
        return file_get_contents('file://' . __DIR__ . '/resources/public.key');
    }

}