<?php

namespace App\Base\HttpFulfillers\OnAfterResponseSentFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Http\OnAfterResponseSent;
use Symfony\Component\HttpFoundation\Response;

class BaseAfterResponseSent implements OnAfterResponseSent
{
    public function OnAfterResponseSent(App $app, Response $response): void
    {
    }
}
