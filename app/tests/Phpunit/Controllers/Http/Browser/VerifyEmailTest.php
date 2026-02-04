<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class VerifyEmailTest extends ControllerCase
{
    function testGetWithValidToken(): void
    {
        $user = $this->createUser('Verify User', 'verify@example.com', 'password123', false);
        $token = bin2hex(random_bytes(32));
        $user->setEmailVerificationToken($token);
        $this->app->getDoctrine()->flush();

        $response = $this->runRequest($this->createGetRequest("/verify-email/r/token/{$token}"));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testGetWithInvalidToken(): void
    {
        $response = $this->runRequest($this->createGetRequest('/verify-email/r/token/invalidtoken123'));

        $this->assertEquals(400, $response->getStatusCode());
    }

    function testGetWithAlreadyVerifiedUser(): void
    {
        $user = $this->createUser('Already Verified', 'alreadyverified@example.com', 'password123', true);
        $token = bin2hex(random_bytes(32));
        $user->setEmailVerificationToken($token);
        $this->app->getDoctrine()->flush();

        $response = $this->runRequest($this->createGetRequest("/verify-email/r/token/{$token}"));

        $this->assertEquals(400, $response->getStatusCode());
    }

}
