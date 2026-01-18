<?php

namespace Hizech\Bliss\Input;

use InvalidArgumentException;

class IntInput implements Choiceable
{

    /**
     * @param array<int, int>|null $choices e.g. [554]
     * @param array<string, string>|null $choices_tids e.g. ['554' => 'general.554' ]
     */
    function __construct(
        public readonly ?array $choices = null,
        public readonly ?array $choices_tids = null,
    )
    {
        // Runtime validation - array_is_list checks are covered by PHPDoc types
        // but kept as defensive checks for non-typed callers
        if ($choices !== null && !array_is_list($choices)) {
            throw new InvalidArgumentException('choices must be flat');
        }
    }

    function validate(string $value): ?int
    {
        $result = filter_var($value, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_THOUSAND);
        $v = $result === false ? null : $result;

        if ($v === null) {
            return null;
        }

        if (is_array($this->choices) && !in_array((string)$v, $this->choices)) {
            return null;
        }

        return $v;

    }


}