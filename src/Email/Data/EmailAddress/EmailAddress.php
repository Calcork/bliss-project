<?php

namespace Hizech\Bliss\Email\Data\EmailAddress;

readonly class EmailAddress
{
    public function __construct(
        public string $email,
        public ?string $name = null,
    ) {}
}