<?php

namespace App\Lib\Input;

readonly class IntInputStructure
{

    function __construct(

        public ?int $min = null,
        public ?int $max = null,

    )
    {}

    function isValid(int $value): bool
    {
        if ($this->min !== null && $value < $this->min) {
            return false;
        }

        if ($this->max !== null && $value > $this->max) {
            return false;
        }

        return true;
    }

}
