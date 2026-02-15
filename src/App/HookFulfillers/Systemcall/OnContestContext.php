<?php

namespace Hizech\Bliss\App\HookFulfillers\Systemcall;

use Hizech\Bliss\App\App;

interface OnContestContext
{

    /**
     * @param string $section
     * @param string $method
     * @param array<string, bool|int|string|float|null> $arguments
     * @param array<string, mixed> $attributes
     * @return array{
     *      section: string,
     *      method: string,
     *      arguments: array<string, float|int|bool|string|null>,
     *      attributes: array<string, mixed>
     *  }|false
     */
    function onContestContext(string $section, string $method, array $arguments, array $attributes) : array|false;

}