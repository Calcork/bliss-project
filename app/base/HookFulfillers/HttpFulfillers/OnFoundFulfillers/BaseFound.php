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

class BaseFound extends HookFulfiller implements OnFound
{

    public function onFound(Request $request, Found|null $routing_result): ControllerHandler|Response|true
    {
        $route = $this->app->getRoutes()->allLinearRoutes()[$routing_result->route];
        $user_id = ($request->getSession()->get('user_id', null) !== null) ? $request->getSession()->get('user_id', null) : null;
        $user = ($user_id !== null) ? $this->app->getDoctrine()->getRepository(User::class)->find($user_id) : null;

        if(in_array('admin', $route->tags)) {
            $can_admin = ($user instanceof User && $user->getIsAdmin() === true);
            if(!$can_admin) return new RedirectResponse($this->app->getEnv()['APP_URL'] . '/' . $this->app->getRoutes()->allLinearRoutes()['r|GET']->toUri());
        }

        return true;
    }

}