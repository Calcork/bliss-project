<?php

namespace Hizech\Bliss\Captcha;

enum ValidationStatus
{
    case Valid;
    case Invalid;
    case Error;
}