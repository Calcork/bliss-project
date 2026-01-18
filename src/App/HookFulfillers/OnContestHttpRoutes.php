<?php

namespace Hizech\Bliss\App\HookFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Route\RouteCollection;

interface OnContestHttpRoutes
{
    function onContestHttpRoutes(App $app, RouteCollection $routes) : RouteCollection;
}