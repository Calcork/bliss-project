<?php

namespace Hizech\Bliss\App\Services;

use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Matcher\Found;
use Hizech\Bliss\Route\Route;
use Hizech\Bliss\Route\RouteCollection;

interface HttpRouter
{
    public function dispatch(HttpMethod $method, string $path): Found | null;

    /** @return array<string, Route> */
    public function getFlatRoutes(): array;
}