<?php

namespace Hizech\Bliss\Email\Data\EmailRecipients;

use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;

class EmailRecipients
{

    /**
     * @var array<int, EmailAddress>
     */
    readonly public array $to;
    /**
     * @var array<int, EmailAddress>
     */
    readonly public array $cc;
    /**
     * @var array<int, EmailAddress>
     */
    readonly public array $bcc;

    /**
     * @param EmailAddress|array<int, EmailAddress> $to
     * @param EmailAddress|array<int, EmailAddress> $cc
     * @param EmailAddress|array<int, EmailAddress> $bcc
     */
    public function __construct(
        EmailAddress|array $to,
        EmailAddress|array $cc = [],
        EmailAddress|array $bcc = [],
    ) {
        if($to instanceof EmailAddress) $this->to = [$to];
        else $this->to = $to;
        if($cc instanceof EmailAddress) $this->cc = [$cc];
        else $this->cc = $cc;
        if($bcc instanceof EmailAddress) $this->bcc = [$bcc];
        else $this->bcc = $bcc;
    }

}