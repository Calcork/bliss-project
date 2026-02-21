<?php

namespace App\Controllers\AppController\SystemCall;

use App\Lib\Migrations\MigrationFactory;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Hizech\Bliss\Controller\SystemcallControllerReport;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Migrations extends SystemCall
{

    private function getFactory(): DependencyFactory
    {
        /** @var array<string, mixed> $config */
        $config = $this->app->getConfig()['migrations'];

        return MigrationFactory::create($this->app->getEntityManager(), $config);
    }

    /** @param string[] $arguments */
    private function run(Command\DoctrineCommand $command, array $arguments): SystemcallControllerReport
    {
        try {
            $command->run(new ArrayInput($arguments), new ConsoleOutput());
            return SystemcallControllerReport::success();
        } catch (\Throwable $e) {
            return SystemcallControllerReport::thrown($e);
        }
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function diff(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\DiffCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function migrate(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\MigrateCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function status(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\StatusCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function latest(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\LatestCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function execute(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\ExecuteCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function generate(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\GenerateCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function rollup(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\RollupCommand($this->getFactory()), $arguments);
    }

    /**
     * @param string[] $arguments
     * @param string[] $attributes
     */
    public function current(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        return $this->run(new Command\CurrentCommand($this->getFactory()), $arguments);
    }

}
