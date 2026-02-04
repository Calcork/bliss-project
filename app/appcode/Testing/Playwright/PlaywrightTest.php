<?php

namespace App\AppCode\Testing\Playwright;

interface PlaywrightTest
{
    /**
     * @return array<string, mixed>
     */
    function generateTestingData() : array;

    /**
     * @param array<string, mixed> $data
     * @return void
     */
    function cleanTestingData(array $data) : void;
    function getTestPath() : string;
}