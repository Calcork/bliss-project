<?php

namespace App\Base\HttpFulfillers\OnContestResponseFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestResponse;
use Symfony\Component\HttpFoundation\Response;

class NormalizeResponse implements OnContestResponse
{

    /**
     * @param App $app
     * @param Response $response
     * @return Response
     */
    function onContestResponse(App $app, Response $response): Response
    {

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        $app_url = $app->getEnv()['APP_URL'] ?? '';

        if (str_starts_with($app_url, 'https://')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;

    }
}