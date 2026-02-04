<?php

namespace App\Tests\Phpunit\Controllers\Http\Api\FrontProvider;

use App\Tests\Phpunit\Controllers\ControllerCase;

class CheckEmailTest extends ControllerCase
{
    function testGetWithAvailableEmail(): void
    {
        $response = $this->runRequest($this->createGetRequest('/api/check-email?email=available@example.com'));

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent() ?: '', true);
        $this->assertTrue($data['available']);
    }

    function testGetWithTakenEmail(): void
    {
        $this->createUser('Taken User', 'taken@example.com', 'password123');

        $response = $this->runRequest($this->createGetRequest('/api/check-email?email=taken@example.com'));

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent() ?: '', true);
        $this->assertFalse($data['available']);
    }

    function testGetWithInvalidEmail(): void
    {
        $response = $this->runRequest($this->createGetRequest('/api/check-email?email=notanemail'));

        $this->assertEquals(422, $response->getStatusCode());
    }

    function testGetWithMissingEmail(): void
    {
        $response = $this->runRequest($this->createGetRequest('/api/check-email'));

        $this->assertEquals(422, $response->getStatusCode());
    }

}
