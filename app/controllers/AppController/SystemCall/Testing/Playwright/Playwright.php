<?php

namespace App\Controllers\AppController\SystemCall\Testing\Playwright;

use App\AppCode\Testing\Playwright\PlaywrightTest;
use App\Controllers\AppController\SystemCall\SystemCall;
use App\Lib\Misc\Misc;
use Hizech\Bliss\Controller\SystemcallControllerReport;
use Symfony\Component\Process\Process;

class Playwright extends SystemCall
{

    /**
     * @param string $section
     * @param string $method
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @param bool[]|float[]|int[]|null[]|string[] $attributes
     */
    function runTestingData(string $section, string $method, array $arguments, array $attributes) : SystemcallControllerReport
    {
        $controllers = $this->resolveControllers($arguments);

        if (count($controllers) === 0) {
            echo "No playwright tests found.\n";
            return SystemcallControllerReport::success();
        }

        $env = $this->app->getEnv();
        $root_path = $this->app->getRootPath();

        $playwright_config = json_encode([
            'app_url' => $env['APP_URL'],
            'storage_dir' => $this->app->getStoragePath(),
        ]);

        $had_failure = false;

        foreach ($controllers as $controller) {
            $data = $controller->generateTestingData();
            $path = $controller->getTestPath();

            echo "--- Running: {$path} ---\n";

            $process_env = array_merge(
                ['PLAYWRIGHT_CONFIG' => $playwright_config],
                Misc::stringifyEnv($data),
            );

            $command = ['npx', 'playwright', 'test', $path];
            $process = new Process($command, $root_path, $process_env);
            $process->setTimeout(300);
            $process->run();

            echo $process->getOutput();

            if (!$process->isSuccessful()) {
                echo $process->getErrorOutput();
                $had_failure = true;
            }

            $controller->cleanTestingData($data);
        }

        return $had_failure
            ? SystemcallControllerReport::ranButFailed('One or more playwright tests failed.')
            : SystemcallControllerReport::success();

    }

    /**
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @return array<int, PlaywrightTest>
     */
    private function resolveControllers(array $arguments) : array
    {
        if (count($arguments) > 0) {
            $controllers = [];
            foreach ($arguments as $argument) {
                /** @var class-string $argument */
                $controllers[] = new $argument($this->app);
            }
            return $controllers;
        }

        return $this->getTestControllers();
    }

    /**
     * @return array<int, PlaywrightTest>
     */
    private function getTestControllers() : array
    {
        $dir = __DIR__ . DIRECTORY_SEPARATOR . 'tests';

        if (!is_dir($dir)) {
            return [];
        }

        $tests = Misc::getRecursiveFilesInDir($dir);
        $controllers = [];

        foreach ($tests as $test) {
            $class = Misc::getClassFromFile($test);
            $controllers[] = new $class($this->app);
        }

        return $controllers;
    }

}