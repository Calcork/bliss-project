<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class HomeTest extends ControllerCase
{
    function testGet(): void
    {
        $response = $this->runRequest($this->createGetRequest('/'));

        $this->assertEquals(200, $response->getStatusCode());
    }

}
