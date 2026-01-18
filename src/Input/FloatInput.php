<?php

namespace Hizech\Bliss\Input;

class FloatInput
{
    function __construct()
    {}

    function validate(string $value): ?float
    {
        $result = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
        return $result === false ? null : $result;
    }

}