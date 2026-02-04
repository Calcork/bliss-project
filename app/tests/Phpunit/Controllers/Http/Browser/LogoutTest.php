<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class LogoutTest extends ControllerCase
{
    function testGetRedirectsToLogin(): void
    {
        $response = $this->runRequest($this->createGetRequest('/logout'));

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('/login', $response->headers->get('location') ?? '');
    }

}
