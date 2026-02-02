<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnAfterResponseSentFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnAfterResponseSent;
use Symfony\Component\HttpFoundation\Response;

class BaseAfterResponseSent extends HookFulfiller implements OnAfterResponseSent
{
    public function OnAfterResponseSent(Response $response): void
    {
    }
}
