<?php

namespace Hizech\Bliss\Controller;

readonly class ControllerHandler {

    function __construct(
        public string $class,
        public string $method
    )
    {}

}