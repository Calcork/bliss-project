<?php

namespace App\Controllers\AppController\Http;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\HttpController;
use Hizech\Bliss\Route\Matcher\Found;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Http implements HttpController
{

    public function __construct(protected App $app, protected Request $request, protected Found|null $routing_result)
    {}

    function preRun(): bool|Response
    {


        return true;
    }

    protected function response() : Response {

        $response = new Response();
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        $app_url = $this->app->getEnv()['APP_URL'] ?? '';

        if (str_starts_with($app_url, 'https://')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;


    }
    public function run(string $method, Request $request, Found|null $routing_result) : Response {

        return $this->{$method}($request, $routing_result);

    }

}
