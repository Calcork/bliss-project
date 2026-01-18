<?php

namespace Hizech\Bliss\Email\EmailProvider;

enum EmailStatus
{

    case BadInputs;
    case Sent;
    case Error;

}