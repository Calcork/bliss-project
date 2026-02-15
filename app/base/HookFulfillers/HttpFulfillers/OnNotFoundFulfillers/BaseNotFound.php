<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnNotFoundFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Controllers\AppController\Http as Controllers;
use Hizech\Bliss\App\HookFulfillers\Http\OnNotFound;
use Hizech\Bliss\Controller\ControllerHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseNotFound  extends HookFulfiller implements OnNotFound
{

    public function onNotFound(Request $request): ControllerHandler|Response
    {
        return new RedirectResponse($this->app->getEnv()['APP_URL'] . $this->app->getRoutes()->allLinearRoutes()['r|GET']->toUri());
    }

}