<?php

namespace App\Controllers\AppController\Http\Browser;

use Symfony\Component\HttpFoundation\Response;

class Home extends Browser
{

    function get() : Response
    {
        return new Response('Home');
    }

}