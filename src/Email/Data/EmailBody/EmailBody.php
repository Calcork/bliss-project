<?php

namespace Hizech\Bliss\Email\Data\EmailBody;

readonly class EmailBody
{

    function __construct(
        public EmailBodyType $type,
        public string $content,
    )
    {}

}