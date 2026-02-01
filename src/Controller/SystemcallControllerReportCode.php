<?php

namespace Hizech\Bliss\Controller;

enum SystemcallControllerReportCode : int
{
    case Success = 0;
    case Thrown = 1;
    case RanButFailed = 2;
}