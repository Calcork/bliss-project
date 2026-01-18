<?php

namespace Hizech\Bliss\Captcha\Cases\NoCaptcha;

use Hizech\Bliss\Captcha\CaptchaProvider;
use Hizech\Bliss\Captcha\ValidationStatus;

class NoCaptcha implements CaptchaProvider
{

    function __construct() {}

    function headHtml() : null {
        return null;
    }

    function FormHtml() : null {
        return null;
    }

    function verifyToken(?string $token, ?string $user_ip = null): ValidationStatus
    {
        return ValidationStatus::Valid;
    }

    function testConnection() : bool {
        return true;
    }

}