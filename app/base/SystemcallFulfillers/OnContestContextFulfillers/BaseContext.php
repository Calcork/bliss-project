<?php

namespace App\Base\SystemcallFulfillers\OnContestContextFulfillers;

use Hizech\Bliss\App\App;
use Hizech\Bliss\App\HookFulfillers\Systemcall\OnContestContext;

class BaseContext implements OnContestContext
{
    /**
     * @param array<string, bool|int|string|float|null> $arguments
     * @param array<string, mixed> $attributes
     * @return array{section: string, method: string, arguments: array<string, float|int|bool|string|null>, attributes: array<string, mixed>}|false
     */
    public function onContestContext(App $app, string $section, string $method, array $arguments, array $attributes): array|false
    {
        return [
            'section' => $section,
            'method' => $method,
            'arguments' => $arguments,
            'attributes' => $attributes
        ];
    }
}
