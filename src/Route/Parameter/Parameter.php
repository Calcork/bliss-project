<?php

namespace Hizech\Bliss\Route\Parameter;

use Hizech\Bliss\Input\RegexValidator;
use Hizech\Bliss\Input\StringValidator;

class Parameter {

    /** @var callable(string): bool */
    private $callback;

    function __construct(

        public readonly Type $type,
        public readonly string $name,
        PremadeCallback $callback

    )
    {
        $this->callback = match($callback) {
            PremadeCallback::Alphabetic   => fn(string $v) => ctype_alpha($v),
            PremadeCallback::Numeric      => fn(string $v) => is_numeric($v),
            PremadeCallback::Alphanumeric => fn(string $v) => ctype_alnum($v),
            PremadeCallback::Slug         => fn(string $v) => (bool)preg_match('/^[A-Za-z0-9_-]+$/', $v),
            PremadeCallback::Any          => fn(string $v) => $v !== '' && !str_contains($v, '/'),
        };
    }

    // validate the parameter string against its callback
    // NOTE - Parameters until given out of the router will always be retrieved as strings - Unconverted
    function validate(string $value) : bool {
        return ($this->callback)($value);
    }
    
}
