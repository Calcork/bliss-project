<?php

namespace App\Base;

use App\Controllers\AppController\SystemCall\Help;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnNotFound;
use Hizech\Bliss\Controller\ControllerHandler;

class SystemcallFulfillers implements OnNotFound
{
    public function onNotFound(App $app, string $section, string $method, array $arguments): ControllerHandler
    {
        return new ControllerHandler(Help::class, 'notFound');
    }
}