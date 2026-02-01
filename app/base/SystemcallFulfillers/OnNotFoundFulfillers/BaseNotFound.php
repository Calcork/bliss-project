<?php

namespace App\Base\SystemcallFulfillers\OnNotFoundFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnNotFound;
use Hizech\Bliss\Controller\ControllerHandler;
use App\Controllers\AppController\SystemCall\Help;

class BaseNotFound implements OnNotFound
{

    public function onNotFound(App $app, string $section, string $method, array $arguments, array $attributes): true|ControllerHandler
    {
        return new ControllerHandler(Help::class, 'notFound');
    }

}