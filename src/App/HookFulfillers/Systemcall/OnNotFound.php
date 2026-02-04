<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\ControllerHandler;

interface OnNotFound
{

    /**
     * @param string $section
     * @param string $method
     * @param array<string, bool|int|string|float|null> $arguments
     * @param array<string, bool|int|string|float|null> $attributes
     * @return ControllerHandler
     */
    function onNotFound(string $section, string $method, array $arguments, array $attributes) : true|ControllerHandler;

}