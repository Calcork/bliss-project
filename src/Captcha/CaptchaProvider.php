<?php

namespace Hizech\Bliss\Captcha;

interface CaptchaProvider
{
    function headHtml() : ?string;
    function FormHtml() : ?string;
    function verifyToken(string $token, ?string $user_ip = null) : ValidationStatus;
    function testConnection() : bool;
}