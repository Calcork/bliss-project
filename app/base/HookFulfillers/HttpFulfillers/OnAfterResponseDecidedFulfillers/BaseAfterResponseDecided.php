<?php

namespace App\Base\HookFulfillers\HttpFulfillers\OnAfterResponseDecidedFulfillers;

use App\Base\HookFulfillers\HookFulfiller;
use Hizech\Bliss\App\HookFulfillers\Http\OnAfterResponseDecided;
use Symfony\Component\HttpFoundation\Response;

class BaseAfterResponseDecided extends HookFulfiller implements OnAfterResponseDecided
{
    public function OnAfterResponseDecided(Response $response): void
    {
    }
}
