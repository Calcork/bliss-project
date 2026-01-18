<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;

interface OnContestContext
{

    /**
     * @param App $app
     * @param string $section
     * @param string $method
     * @param array<string, bool|int|string|float|null> $arguments
     * @return array{
     *      section: string,
     *      method: string,
     *      arguments: array<string, float|int|bool|string|null>
     *  }
     */
    function onContestContext(App $app, string $section, string $method, array $arguments) : array;

}