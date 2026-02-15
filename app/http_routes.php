<?php

use App\Controllers\AppController\Http as HttpControllers;
use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Parameter\Parameter;
use Hizech\Bliss\Route\Parameter\PremadeCallback;
use Hizech\Bliss\Route\Parameter\Type as ParameterType;
use Hizech\Bliss\Route\Route;
use Hizech\Bliss\Route\RouteCollection;

$route_collection  = new RouteCollection();

// Http

{

$route_collection->add('root', new Route([], ));

}

// Admin
{

    $route_collection->add('admin', new RouteCollection(
        ['admin'],
        [],
        ['admin']
    )->addMany([

    ]));

}

// Api
{

    $route_collection->add('r|api',  new RouteCollection()->addMany([

        ]));

}

return $route_collection;