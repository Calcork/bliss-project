<?php

namespace Hizech\Bliss\HttpRouter;

// practical case
use Hizech\Bliss\Route\HttpMethod;

class ApiReference
{
    function __construct(
        public readonly string $route_name,
        public readonly HttpMethod $http_method,
    ) {}
}