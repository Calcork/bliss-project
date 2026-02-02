<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnContestResponseFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseResponse extends HookFulfiller implements OnContestResponse
{
    public function onContestResponse(Response $response): Response
    {
        return $response;
    }
}
