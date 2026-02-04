<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class ExampleTest extends ControllerCase
{
    function testGet(): void
    {
        $response = $this->runRequest($this->createGetRequest('/example'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hi', $response->getContent());
    }

}
