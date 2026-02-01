<?php

namespace App\Controllers\AppController\SystemCall;

use App\Lib\Migrations\MigrationFactory;
use Doctrine\Migrations\Tools\Console\Command;
use Hizech\Bliss\Controller\SystemcallControllerReport;
use Symfony\Component\Console\Command\Command as ConsoleCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Migrations extends SystemCall
{
    private function getDependencyFactory(): \Doctrine\Migrations\DependencyFactory
    {
        $em = $this->app->getDbEntityManager();
        $config = $this->app->getBag()['config']['migrations'];
        return MigrationFactory::create($em, $config);
    }

    private function runCommand(ConsoleCommand $command): SystemcallControllerReport
    {
        $command->setName($this->section . ':' . $this->method);

        $input_args = [];
        foreach ($this->arguments as $key => $value) {
            $input_args[str_starts_with($key, '--') ? $key : "--{$key}"] = $value;
        }

        $input = new ArrayInput($input_args);
        $input->setInteractive(false);

        $exit_code = $command->run($input, new ConsoleOutput());

        return $exit_code === 0
            ? SystemcallControllerReport::Success()
            : SystemcallControllerReport::ranButFailed("Exited with code {$exit_code}");
    }

    public function diff(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\DiffCommand($this->getDependencyFactory()));
    }

    public function migrate(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\MigrateCommand($this->getDependencyFactory()));
    }

    public function status(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\StatusCommand($this->getDependencyFactory()));
    }

    public function latest(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\LatestCommand($this->getDependencyFactory()));
    }

    public function execute(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\ExecuteCommand($this->getDependencyFactory()));
    }

    public function generate(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\GenerateCommand($this->getDependencyFactory()));
    }

    public function rollup(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\RollupCommand($this->getDependencyFactory()));
    }

    public function current(): SystemcallControllerReport
    {
        return $this->runCommand(new Command\CurrentCommand($this->getDependencyFactory()));
    }
}
