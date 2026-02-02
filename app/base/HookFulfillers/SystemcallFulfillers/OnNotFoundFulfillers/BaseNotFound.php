<?php

namespace App\Base\HookFulfillers\SystemcallFulfillers\OnNotFoundFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Controllers\AppController\SystemCall\Help;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnNotFound;
use Hizech\Bliss\Controller\ControllerHandler;

class BaseNotFound extends HookFulfiller implements OnNotFound
{

    public function onNotFound(string $section, string $method, array $arguments, array $attributes): true|ControllerHandler
    {
        return new ControllerHandler(Help::class, 'notFound');
    }

}