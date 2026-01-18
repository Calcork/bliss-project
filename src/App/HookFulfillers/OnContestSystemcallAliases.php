<?php

namespace Hizech\Bliss\App\HookFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\ControllerHandler;

interface OnContestSystemcallAliases
{
    /**
     * @param App $app
     * @param array<string, array<string, ControllerHandler>> $systemcall_aliases
     * @return array<string, array<string, ControllerHandler>>
     */
    function onContestSystemcallAliases(App $app, array $systemcall_aliases) : array;
}