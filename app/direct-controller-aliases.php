<?php

use App\Controllers\DirectController as Controllers;
use Hizech\Bliss\Controller\ControllerHandler;

$direct_controller_aliases = [

    'Example' => [
        'example' => new ControllerHandler(Controllers\Example::class, 'example'),
    ],

];

return $direct_controller_aliases;