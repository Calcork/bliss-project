<?php

use App\Controllers\AppController\Http as HttpControllers;
use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Route;
use Hizech\Bliss\Route\RouteCollection;

$route_collection  = new RouteCollection();

// Http

{

    $route_collection->add('r|example|GET',
        new Route(['example'], new ControllerHandler(HttpControllers\Browser\Example::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|GET',
        new Route([], new ControllerHandler(HttpControllers\Browser\Home::class, 'get'), [HttpMethod::GET])
    );

}

// Api
{

}

return $route_collection;