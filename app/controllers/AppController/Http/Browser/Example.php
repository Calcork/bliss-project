<?php

namespace App\Controllers\AppController\Http\Browser;

use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Example extends Browser
{

    function get(Request $request, Found|null $routing_result) : Response
    {
        return new Response('Hi');
    }

}