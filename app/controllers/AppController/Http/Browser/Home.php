<?php

namespace App\Controllers\AppController\Http\Browser;

use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home extends Browser
{

    function get(Request $request, Found|null $routing_result) : Response
    {
        $is_logged_in = $request->getSession()->has('user_id');

        $html = $this->app->getTemplateMaster()->twigCustomRender('home.twig', [
            'is_logged_in' => $is_logged_in,
            'attributes' => $request->attributes,
        ]);

        return new Response($html);
    }

}