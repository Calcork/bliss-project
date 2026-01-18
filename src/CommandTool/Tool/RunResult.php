<?php

namespace Hizech\Bliss\CommandTool\Tool;

class RunResult
{

    public function __construct(
        public readonly ?string $output,
        public readonly ?int $exit_code = null,
        public readonly ?string $error_output = null,
    ) {}

}