<?php

use App\Base\App;
use Symfony\Component\HttpFoundation\Request;

require_once(__DIR__ . '/../vendor/autoload.php');

// Create request and dispatch
$request = Request::createFromGlobals();

// Boot the application
$app = new App();

$app->runHttp($request);