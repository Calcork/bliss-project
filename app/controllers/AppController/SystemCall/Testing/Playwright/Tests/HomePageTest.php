<?php

namespace App\Controllers\AppController\SystemCall\Testing\Playwright\Tests;

use App\AppCode\Testing\Playwright\PlaywrightTest;
use App\Base\App;

class HomePageTest implements PlaywrightTest
{
    public function __construct(private App $app) {}

    public function generateTestingData() : array
    {
        return [
            'TEST_URL' => $this->app->getEnv()['APP_URL'],
        ];
    }

    public function cleanTestingData(array $data) : void
    {
    }

    public function getTestPath() : string
    {
        return 'app/tests/playwright/home-page.spec.js';
    }
}
