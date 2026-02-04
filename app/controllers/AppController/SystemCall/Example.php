<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\Controller\SystemcallControllerReport;

class Example extends SystemCall
{
    /**
     * @param string $section
     * @param string $method
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @param bool[]|float[]|int[]|null[]|string[] $attributes
     */
    function example(string $section, string $method, array $arguments, array $attributes) : SystemcallControllerReport
    {
        echo 'Bla';
        return SystemcallControllerReport::success();
    }
}