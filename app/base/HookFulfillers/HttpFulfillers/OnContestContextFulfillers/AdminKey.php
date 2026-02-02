<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestContextFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminKey extends HookFulfiller implements OnContestContext
{

    /**
     * @param App $app
     * @param Request $request
     * @return Request|Response
     */
    function onContestContext(Request $request): Request|Response
    {

        $has_admin_key = $request->cookies->has('admin_key') && $request->cookies->get('admin_key', null) === $app->getEnv()['ADMIN_KEY'];
        $request->attributes->add(['has_admin_key' => $has_admin_key]);

        return $request;

    }

}