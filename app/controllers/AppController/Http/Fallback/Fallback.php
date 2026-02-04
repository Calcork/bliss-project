<?php

namespace App\Controllers\AppController\Http\Fallback;

use App\Controllers\AppController\Http\Http;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Fallback extends Http
{

    function get(Request $request, Found|null $routing_result) : Response
    {
       return new Response('Not Found', 404);
    }

}