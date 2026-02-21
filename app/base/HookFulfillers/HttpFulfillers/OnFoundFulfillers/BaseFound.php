<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnFoundFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use App\Models\User;
use Hizech\Bliss\App\HookFulfillers\Http\OnFound;
use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class BaseFound extends HookFulfiller implements OnFound
{

    public function onFound(Request $request, Found|null $routing_result): ControllerHandler|Response|true
    {

        if(!$request->hasSession()) {


            $storage = new NativeSessionStorage([

                'cookie_httponly' => true,
                'cookie_secure' => !$this->app->getEnv()['APP_DEVELOPMENT'],
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
                'use_only_cookies' => true,
                'cookie_path' => '/',

            ], $this->app->getSessionHandler());

            $session = new Session($storage);
            $request->setSession($session);
            $session->start();

            $request->setSession($session);

        }

        $route = $this->app->getRoutes()->allLinearRoutes()[$routing_result->route];
        $user_id = ($request->getSession()->get('user_id', null) !== null) ? $request->getSession()->get('user_id', null) : null;
        $user = ($user_id !== null) ? $this->app->getEntityManager()->getRepository(User::class)->find($user_id) : null;

        if(in_array('admin', $route->tags)) {
            $can_admin = ($user instanceof User && $user->getIsAdmin() === true);
            if(!$can_admin) return new RedirectResponse($this->app->getEnv()['APP_URL'] . '/' . $this->app->getRoutes()->allLinearRoutes()['r|GET']->toUri());
        }

        return true;
    }

}