<?php

namespace App\Base\HttpFulfillers\OnNotFoundFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnNotFound;
use Hizech\Bliss\Controller\ControllerHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Controllers\AppController\Http as Controllers;

class BaseNotFound implements OnNotFound
{

    public function onNotFound(App $app, Request $request): ControllerHandler|Response
    {
        return new ControllerHandler(Controllers\Browser\NotFound::class, 'get');
    }

}