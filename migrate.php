<?php

use App\Base\App;
use App\Lib\Migrations\MigrationFactory;
use Doctrine\Migrations\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/vendor/autoload.php';

$app = new App(__DIR__);
$factory = MigrationFactory::create($app->getDoctrine(), $app->getConfig()['migrations']);

ConsoleRunner::run([], $factory);
