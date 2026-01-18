<?php

namespace Hizech\Bliss\Input;

use InvalidArgumentException;

class StringInput implements Choiceable
{
    /**
     * @param array<int, string>|null $choices e.g ['mailhog']
     * @param array<string, string>|null $choices_tids e.g ['mailhog' => 'general.mailhog']
     */
    function __construct(
        public readonly ?string $regex = null,
        public readonly ?int               $min_length = null,
        public readonly ?int               $max_length = null,
        public readonly ?array $choices = null,
        public readonly ?array $choices_tids = null,
    )
    {
        // Runtime validation - kept for non-typed callers
        if ($choices !== null && !array_is_list($choices)) {
            throw new InvalidArgumentException('choices must be flat');
        }
    }

    function validate(string $value) : ?string {

        $valid = ($this->min_length === null || mb_strlen($value) >= $this->min_length)
            && ($this->max_length === null || mb_strlen($value) <= $this->max_length)
            && ($this->regex === null || preg_match($this->regex, $value) === 1);

        if (!$valid) return null;

        if (is_array($this->choices) && !in_array($value, $this->choices)) {
            return null;
        }

        return $value;

    }

}