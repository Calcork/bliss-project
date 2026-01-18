<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\ControllerHandler;

interface OnNotFound
{

    /**
     * @param App $app
     * @param string $section
     * @param string $method
     * @param array<string, bool|int|string|float|null> $arguments
     * @return ControllerHandler
     */
    function onNotFound(App $app, string $section, string $method, array $arguments) : ControllerHandler;

}