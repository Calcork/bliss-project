<?php

namespace App\Controllers\AppController\Http\Browser\Front;

use App\Controllers\AppController\Http\Browser\Browser;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Home extends Browser
{
    function get(Request $request, Found|null $routing_result) : Response
    {

        $context = [
          'request' => $request,
           'routing_result' => $routing_result,
        ];

        return new Response($this->app->getTemplateMaster()->twigCustomRender('browser/front/home.html.twig', $context));

    }

}