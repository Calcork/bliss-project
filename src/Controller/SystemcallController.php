<?php

namespace Hizech\Bliss\Controller;

use Hizech\Bliss\App\App;

interface SystemcallController
{

    /**
     * @param App $app
     * @param array<string, float|bool|int|null|string> $arguments
     * @param array<string, float|bool|int|null|string> $attributes
     */
    public function __construct(App $app, string $section, string $method, array $arguments, array $attributes);

}
