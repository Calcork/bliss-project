<?php

namespace App\Controllers\AppController\Http\Fallback;

use App\Controllers\AppController\Http\Http;
use Symfony\Component\HttpFoundation\Response;

class Fallback extends Http
{

    function get() : Response
    {
       return new Response('Not Found', 404);
    }

}