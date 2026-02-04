<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class LoginTest extends ControllerCase
{
    function testGetRendersForm(): void
    {
        $response = $this->runRequest($this->createGetRequest('/login'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    function testPostWithMissingFields(): void
    {
        $response = $this->runRequest($this->createPostRequest('/login'));

        $this->assertEquals(422, $response->getStatusCode());
    }

    function testPostWithInvalidCredentials(): void
    {
        $response = $this->runRequest($this->createPostRequest('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrongpassword',
        ]));

        $this->assertEquals(401, $response->getStatusCode());
    }

    function testPostWithValidCredentials(): void
    {
        $this->createUser('Login User', 'login@example.com', 'password123');

        $response = $this->runRequest($this->createPostRequest('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostWithUnverifiedEmail(): void
    {
        $this->createUser('Unverified', 'unverified@example.com', 'password123', false);

        $response = $this->runRequest($this->createPostRequest('/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]));

        $this->assertEquals(403, $response->getStatusCode());
    }

}
