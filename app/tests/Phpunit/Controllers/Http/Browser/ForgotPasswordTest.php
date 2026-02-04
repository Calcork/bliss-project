<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class ForgotPasswordTest extends ControllerCase
{
    function testGetRendersForm(): void
    {
        $response = $this->runRequest($this->createGetRequest('/forgot-password'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    function testPostWithInvalidEmail(): void
    {
        $response = $this->runRequest($this->createPostRequest('/forgot-password'));

        $this->assertEquals(422, $response->getStatusCode());
    }

    function testPostWithValidEmailRedirects(): void
    {
        $this->createUser('Forgot User', 'forgot@example.com', 'password123');

        $response = $this->runRequest($this->createPostRequest('/forgot-password', [
            'email' => 'forgot@example.com',
        ]));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostWithNonexistentEmailStillRedirects(): void
    {
        $response = $this->runRequest($this->createPostRequest('/forgot-password', [
            'email' => 'nobody@example.com',
        ]));

        $this->assertEquals(302, $response->getStatusCode());
    }

}
