<?php

namespace App\Tests\Phpunit\Controllers\Http\Browser;

use App\Tests\Phpunit\Controllers\ControllerCase;

class SetLocaleTest extends ControllerCase
{
    function testGetWithValidLocaleSetsCookie(): void
    {
        $response = $this->runRequest($this->createGetRequest('/set-locale/r/locale/en'));

        $this->assertEquals(302, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $locale_cookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'locale') {
                $locale_cookie = $cookie;
            }
        }

        $this->assertNotNull($locale_cookie);
        $this->assertEquals('en', $locale_cookie->getValue());
    }

    function testGetWithInvalidLocaleDoesNotSetCookie(): void
    {
        $response = $this->runRequest($this->createGetRequest('/set-locale/r/locale/zz'));

        $this->assertEquals(302, $response->getStatusCode());

        $cookies = $response->headers->getCookies();
        $locale_cookie = null;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'locale') {
                $locale_cookie = $cookie;
            }
        }

        $this->assertNull($locale_cookie);
    }

}
