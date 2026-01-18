<?php

namespace Hizech\Bliss\App\Services;

use Throwable;

interface Logger
{

    public function log(string $channel, Throwable $content): void;

}