<?php

namespace Hizech\Bliss\Controller;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Route\Matcher\Found;
use Hizech\Bliss\Route\Matcher\NotFound;
use Symfony\Component\HttpFoundation\Request;

interface HttpController
{
    public function __construct(App $app, Request $request, Found|null $routing_result);
}
