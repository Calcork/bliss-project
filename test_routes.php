<?php

require 'vendor/autoload.php';

$routes = require 'app/http_routes.php';

foreach ($routes->allLinearRoutes() as $name => $route) {
    if (strpos($name, 'entity') !== false) {
        echo $name . ' => ' . $route->toRegex() . PHP_EOL;
    }
}
