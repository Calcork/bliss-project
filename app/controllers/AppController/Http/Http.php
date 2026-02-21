<?php

namespace App\Controllers\AppController\Http;

use App\Base\App;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Http
{

    public function __construct(protected App $app)
    {}

}
