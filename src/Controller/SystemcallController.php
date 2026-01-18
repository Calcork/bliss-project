<?php

namespace Hizech\Bliss\Controller;

use Hizech\Bliss\App\App;

interface SystemcallController
{

    /**
     * @param App $app
     * @param array<string, float|bool|int|null|string> $args
     */
    public function __construct(App $app, string $section, string $method, array $args);

}
