<?php

namespace Hizech\Bliss\Cli\ArgStructure\Input;

class Variable
{

    function __construct(
        public readonly ?string $regex,
    ) {}

}