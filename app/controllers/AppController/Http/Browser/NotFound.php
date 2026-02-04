<?php

namespace App\Controllers\AppController\Http\Browser;

use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NotFound extends Browser
{
    function get(Request $request, Found|null $routing_result) : Response
    {
        return new Response('Not Found', 404);
    }
}