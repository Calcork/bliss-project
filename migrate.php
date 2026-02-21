<?php

use App\Base\App;
use App\Lib\Migrations\MigrationFactory;
use Doctrine\Migrations\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/vendor/autoload.php';

$app = new App();
$factory = MigrationFactory::create($app->getEntityManager(), $app->getConfig()['migrations']);

ConsoleRunner::run([], $factory);
