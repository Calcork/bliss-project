<?php

namespace App\Controllers\AppController\Http\Browser;

use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Logout extends Browser
{

    function get(Request $request, Found|null $routing_result): Response
    {
        $request->getSession()->invalidate();

        $app_url = $this->app->getEnv()['APP_URL'];
        $login_url = $app_url . $this->app->getRoutes()->all()['r|login|GET']->toUri();

        return new RedirectResponse($login_url, 302);
    }

}
