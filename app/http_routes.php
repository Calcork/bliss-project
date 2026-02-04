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

    $route_collection->add('r|example|GET',
        new Route(['example'], new ControllerHandler(HttpControllers\Browser\Example::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|GET',
        new Route([], new ControllerHandler(HttpControllers\Browser\Home::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|login|GET',
        new Route(['login'], new ControllerHandler(HttpControllers\Browser\Login::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|login|POST',
        new Route(['login'], new ControllerHandler(HttpControllers\Browser\Login::class, 'post'), [HttpMethod::POST])
    );

    $route_collection->add('r|register|GET',
        new Route(['register'], new ControllerHandler(HttpControllers\Browser\Register::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|register|POST',
        new Route(['register'], new ControllerHandler(HttpControllers\Browser\Register::class, 'post'), [HttpMethod::POST])
    );

    $route_collection->add('r|dashboard|GET',
        new Route(['dashboard'], new ControllerHandler(HttpControllers\Browser\Dashboard::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|dashboard|POST',
        new Route(['dashboard'], new ControllerHandler(HttpControllers\Browser\Dashboard::class, 'post'), [HttpMethod::POST])
    );

    $route_collection->add('r|set-locale|GET',
        new Route(['set-locale'], new ControllerHandler(HttpControllers\Browser\SetLocale::class, 'get'), [HttpMethod::GET], [
            'locale' => new Parameter(ParameterType::Required, 'locale', PremadeCallback::Alphabetic),
        ])
    );

    $route_collection->add('r|logout|GET',
        new Route(['logout'], new ControllerHandler(HttpControllers\Browser\Logout::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|forgot-password|GET',
        new Route(['forgot-password'], new ControllerHandler(HttpControllers\Browser\ForgotPassword::class, 'get'), [HttpMethod::GET])
    );

    $route_collection->add('r|forgot-password|POST',
        new Route(['forgot-password'], new ControllerHandler(HttpControllers\Browser\ForgotPassword::class, 'post'), [HttpMethod::POST])
    );

    $route_collection->add('r|reset-password|GET',
        new Route(['reset-password'], new ControllerHandler(HttpControllers\Browser\ResetPassword::class, 'get'), [HttpMethod::GET], [
            'token' => new Parameter(ParameterType::Required, 'token', PremadeCallback::Alphanumeric),
        ])
    );

    $route_collection->add('r|reset-password|POST',
        new Route(['reset-password'], new ControllerHandler(HttpControllers\Browser\ResetPassword::class, 'post'), [HttpMethod::POST])
    );

    $route_collection->add('r|verify-email|GET',
        new Route(['verify-email'], new ControllerHandler(HttpControllers\Browser\VerifyEmail::class, 'get'), [HttpMethod::GET], [
            'token' => new Parameter(ParameterType::Required, 'token', PremadeCallback::Alphanumeric),
        ])
    );

}

// Api
{

    $route_collection->add('r|api|check-email|GET',
        new Route(['api', 'check-email'], new ControllerHandler(HttpControllers\Api\FrontProvider\CheckEmail::class, 'get'), [HttpMethod::GET])
    );

}

return $route_collection;