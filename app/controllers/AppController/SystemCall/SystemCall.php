<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\SystemcallController;
use Hizech\Bliss\Controller\SystemcallControllerReport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class SystemCall implements SystemcallController
{

    /**
     * @param array<string, string|int|bool|float|null> $arguments
     * @param array<string, mixed> $attributes
     * @return void
     */
    public function __construct(protected App $app, protected string $section, protected string $method, protected array $arguments, protected array $attributes)
    {}

}
