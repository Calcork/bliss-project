<?php

namespace App\Lib\Input;

readonly class StringInputStructure
{
    function __construct(

        // Without delimiters, bad - '/^.*$/u', good - '.*'
        public ?string $regex = null,
        public ?int $min_length = null,
        public ?int $max_length = null,

    )
    {}

    function isValid(string $value): bool
    {
        $length = mb_strlen($value);

        if ($this->min_length !== null && $length < $this->min_length) {
            return false;
        }

        if ($this->max_length !== null && $length > $this->max_length) {
            return false;
        }

        if ($this->regex !== null && !preg_match('/^' . $this->regex . '$/u', $value)) {
            return false;
        }

        return true;
    }

}