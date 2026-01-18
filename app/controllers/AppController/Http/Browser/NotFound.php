<?php

namespace App\Controllers\AppController\Http\Browser;

use Symfony\Component\HttpFoundation\Response;

class NotFound extends Browser
{
    function get() : Response
    {
        return new Response('Not Found', 404);
    }
}