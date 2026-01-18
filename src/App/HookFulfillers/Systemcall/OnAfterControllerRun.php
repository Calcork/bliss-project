<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;

interface OnAfterControllerRun
{
    function onAfterControllerRun(App $app) : void;
}