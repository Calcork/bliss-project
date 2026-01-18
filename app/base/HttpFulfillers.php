<?php

namespace App\Base;

use App\Controllers\AppController\Http as Controllers;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnNotFound;
use Hizech\Bliss\Controller\ControllerHandler;
use Symfony\Component\HttpFoundation\Request;

class HttpFulfillers implements OnNotFound
{

    public function onNotFound(App $app, Request $request): ControllerHandler
    {
        return new ControllerHandler(Controllers\Browser\NotFound::class, 'get');
    }

}