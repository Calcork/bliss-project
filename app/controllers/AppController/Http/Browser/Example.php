<?php

namespace App\Controllers\AppController\Http\Browser;

use Symfony\Component\HttpFoundation\Response;

class Example extends Browser
{

    function get() : Response
    {
        return new Response('Hi');
    }

}