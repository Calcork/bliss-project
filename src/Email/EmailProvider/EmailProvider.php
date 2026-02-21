<?php

namespace Hizech\Bliss\Email\EmailProvider;

use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;

interface EmailProvider
{

    function sendEmail(EmailAddress $from, EmailRecipients $to, string $subject, EmailBody $body) : bool;
    function testConnection(): bool;
}