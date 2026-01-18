<?php

namespace Hizech\Bliss\Cli\ArgStructure\Input;

use ErrorException;

class Path
{

    function __construct(public readonly bool $allow_absolute, public readonly bool $allow_relative)  {
        if(!$this->allow_absolute && !$this->allow_relative) throw new ErrorException("cant forbid both relative AND absolute paths");
    }

}