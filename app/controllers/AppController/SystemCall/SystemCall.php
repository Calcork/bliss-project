<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\SystemcallController;

class SystemCall implements SystemcallController
{

    /**
     * @param array<string, string|int|bool|float|null> $args
     * @return void
     */
    public function __construct(protected App $app, protected string $section, protected string $method, protected array $args)
    {}

}
