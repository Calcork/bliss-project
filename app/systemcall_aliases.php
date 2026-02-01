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
    ],
    'Migrations' =>
    [
        'diff' => new ControllerHandler(Controllers\Migrations::class, 'diff'),
        'migrate' => new ControllerHandler(Controllers\Migrations::class, 'migrate'),
        'status' => new ControllerHandler(Controllers\Migrations::class, 'status'),
        'latest' => new ControllerHandler(Controllers\Migrations::class, 'latest'),
        'execute' => new ControllerHandler(Controllers\Migrations::class, 'execute'),
        'generate' => new ControllerHandler(Controllers\Migrations::class, 'generate'),
        'rollup' => new ControllerHandler(Controllers\Migrations::class, 'rollup'),
        'current' => new ControllerHandler(Controllers\Migrations::class, 'current'),
    ],
];

return $systemcall_aliases;