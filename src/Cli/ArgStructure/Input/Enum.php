<?php

namespace Hizech\Bliss\Cli\ArgStructure\Input;

use ErrorException;

class Enum
{
    /** @var array<int, string> */
    private array $values;

    /**
     * @param array<int, string> $values
     */
    public function __construct(array $values) {
        $this->values = $values;
    }

    /**
     * @return array<int, string>
     */
    public function getValues(): array {
        return $this->values;
    }
}
