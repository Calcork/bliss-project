<?php

use App\Base\App;
use Hizech\Bliss\Argv\Util as ArgvUtil;
use Hizech\Bliss\Misc\Util as MiscUtil;

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

require_once(__DIR__ . '/vendor/autoload.php');

/**
 * php cli.php Controller:someMethod --is_santa_cool$
 * php cli.php DirectController:someMethod --_type=direct
 * php cli.php sprintf --_type=callable --format="hi this is really cook"
 */

// Parse CLI arguments
$argv_data = ArgvUtil::analyzeArgv($argv);

$arguments = $argv_data['arguments'];
$special_arguments = $argv_data['special_arguments'];
$section = $argv_data['section'];
$method = $argv_data['method'];
$callable = $argv_data['callable'];

$type = (isset($special_arguments['_type'])) ? $special_arguments['_type'] : null;

if ($type === 'direct') {

    // Direct Controller Mode - bypasses application boot
    $dc_aliases = require(__DIR__ . MiscUtil::pathByParts('/app', '/direct-controller-aliases.php'));
    $controller_handler = $dc_aliases[$section][$method] ?? null;

    if ($controller_handler === null) {
        throw new ErrorException("Direct controller not found: {$section}:{$method}");
    }

    /**
     * @var \App\Controllers\DirectController\DirectController $controller
     */
    $controller = new ($controller_handler->class)($arguments);

}

// Else cause we need an app instance for both
else {

    $app = new App(__DIR__);

    if($type === 'callable'){

        // echo defaults to true
        $echo = (isset($special_arguments['_echo'])) ? $special_arguments['_echo'] : true;
        $app->runCallable($echo, $callable, $arguments);

    }

    // If regular systemcall
    else {
        $app->runSystemcall($section, $method, $arguments);
    }

}