<?php

namespace App\Controllers\DirectController;

abstract class DirectController implements \Hizech\Bliss\Controller\DirectController
{

    function __construct(protected array $args)
    {}

}