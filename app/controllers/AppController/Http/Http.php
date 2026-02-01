<?php

namespace App\Controllers\AppController\Http;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\HttpController;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;

class Http implements HttpController
{

    public function __construct(protected App $app, protected Request $request, protected Found|null $routing_result)
    {}

}
