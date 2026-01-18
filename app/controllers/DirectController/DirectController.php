<?php

namespace App\Controllers\DirectController;

use Hizech\Bliss\App\App;
use Hizech\Bliss\Controller\SystemcallController;

abstract class DirectController implements \Hizech\Bliss\Controller\DirectController
{

    function __construct(protected array $args)
    {}

}