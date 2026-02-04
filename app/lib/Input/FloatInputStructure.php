<?php

namespace App\Lib\Input;

readonly class FloatInputStructure
{

    function __construct(

        public ?float $min = null,
        public ?float $max = null,

    )
    {}

    function isValid(float $value): bool
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
