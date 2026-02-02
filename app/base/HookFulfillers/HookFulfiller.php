<?php

namespace App\Base\HookFulfillers;

use App\Base\App;

class HookFulfiller
{
    function __construct(protected App $app)
    {}
}