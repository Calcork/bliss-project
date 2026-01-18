<?php

use App\Controllers\AppController\SystemCall as Controllers;
use Hizech\Bliss\Controller\ControllerHandler;

$systemcall_aliases = [

    'Example' => [
        'example' => new ControllerHandler(Controllers\Example::class, 'example'),
    ],
    'Help' => [
        'listSystemcalls' => new ControllerHandler(Controllers\Help::class, 'listSystemcalls'),
    ],
    'Cache' =>
    [
        'purgeAll' => new ControllerHandler(Controllers\Cache::class, 'purgeAll'),
        'rebuildAll' => new ControllerHandler(Controllers\Cache::class, 'rebuildAll'),
    ]
];

return $systemcall_aliases;