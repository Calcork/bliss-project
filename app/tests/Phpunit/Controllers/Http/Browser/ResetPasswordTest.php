<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class ResetPasswordTest extends ControllerCase
{
    function testGetWithValidToken(): void
    {
        $user = $this->createUser('Reset User', 'reset@example.com', 'password123');
        $token = bin2hex(random_bytes(32));
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiresAt(new \DateTimeImmutable('+1 hour'));
        $this->app->getDoctrine()->flush();

        $response = $this->runRequest($this->createGetRequest("/reset-password/r/token/{$token}"));

        $this->assertEquals(200, $response->getStatusCode());
    }

    function testGetWithInvalidToken(): void
    {
        $response = $this->runRequest($this->createGetRequest('/reset-password/r/token/invalidtoken123'));

        $this->assertEquals(400, $response->getStatusCode());
    }

    function testGetWithExpiredToken(): void
    {
        $user = $this->createUser('Expired User', 'expired@example.com', 'password123');
        $token = bin2hex(random_bytes(32));
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiresAt(new \DateTimeImmutable('-1 hour'));
        $this->app->getDoctrine()->flush();

        $response = $this->runRequest($this->createGetRequest("/reset-password/r/token/{$token}"));

        $this->assertEquals(400, $response->getStatusCode());
    }

    function testPostWithValidTokenAndPassword(): void
    {
        $user = $this->createUser('ResetPost User', 'resetpost@example.com', 'password123');
        $token = bin2hex(random_bytes(32));
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiresAt(new \DateTimeImmutable('+1 hour'));
        $this->app->getDoctrine()->flush();

        $response = $this->runRequest($this->createPostRequest('/reset-password', [
            'token' => $token,
            'password' => 'newpassword123',
        ]));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostWithInvalidToken(): void
    {
        $response = $this->runRequest($this->createPostRequest('/reset-password', [
            'token' => 'badtoken',
            'password' => 'newpassword123',
        ]));

        $this->assertEquals(400, $response->getStatusCode());
    }

    function testPostWithMissingPassword(): void
    {
        $user = $this->createUser('NoPw User', 'nopw@example.com', 'password123');
        $token = bin2hex(random_bytes(32));
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiresAt(new \DateTimeImmutable('+1 hour'));
        $this->app->getDoctrine()->flush();

        $response = $this->runRequest($this->createPostRequest('/reset-password', [
            'token' => $token,
        ]));

        $this->assertEquals(422, $response->getStatusCode());
    }

}
