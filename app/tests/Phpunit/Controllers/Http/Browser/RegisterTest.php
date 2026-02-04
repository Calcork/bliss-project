<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class RegisterTest extends ControllerCase
{
    function testGetRendersForm(): void
    {
        $response = $this->runRequest($this->createGetRequest('/register'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    function testPostWithMissingFields(): void
    {
        $response = $this->runRequest($this->createPostRequest('/register'));

        $this->assertEquals(422, $response->getStatusCode());
    }

    function testPostWithValidData(): void
    {
        $response = $this->runRequest($this->createPostRequest('/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
        ]));

        $this->assertEquals(302, $response->getStatusCode());
    }

    function testPostWithDuplicateEmail(): void
    {
        $this->createUser('Existing', 'duplicate@example.com', 'password123');

        $response = $this->runRequest($this->createPostRequest('/register', [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
        ]));

        $this->assertEquals(409, $response->getStatusCode());
    }

}
