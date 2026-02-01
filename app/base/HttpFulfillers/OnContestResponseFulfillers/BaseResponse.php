<?php

namespace App\Base\HttpFulfillers\OnContestResponseFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseResponse implements OnContestResponse
{
    public function onContestResponse(App $app, Response $response): Response
    {
        return $response;
    }
}
