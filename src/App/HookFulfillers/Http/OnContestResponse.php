<?php

namespace Hizech\Bliss\App\HookFulfillers\Http;

use Hizech\Bliss\App\App;
use Symfony\Component\HttpFoundation\Response;

interface OnContestResponse {
    function onContestResponse(App $app, Response $response) : Response;
}