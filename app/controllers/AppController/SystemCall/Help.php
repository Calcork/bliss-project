<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\Controller\SystemcallControllerReport;

class Help extends SystemCall
{

    /**
     * @param string $section
     * @param string $method
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @param bool[]|float[]|int[]|null[]|string[] $attributes
     */
    function notFound(string $section, string $method, array $arguments, array $attributes) : SystemcallControllerReport
    {
        echo sprintf('The command: %s:%s was not found, please run Help:listSystemcalls to list all registered commands.', $section, $method);
        return SystemcallControllerReport::success();
    }

    /**
     * @param string $section
     * @param string $method
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @param bool[]|float[]|int[]|null[]|string[] $attributes
     */
    function listSystemcalls(string $section, string $method, array $arguments, array $attributes) : SystemcallControllerReport {

        echo 'The following commands are registered for systemcall aliases:' . PHP_EOL . PHP_EOL;

        foreach($this->app->getSystemCallAliases() as $section => $array) {

            foreach ($array as $method => $command) {

                $str = $section . ':' . $method;
                echo $str . PHP_EOL;

            }

        }

        return SystemcallControllerReport::success();

    }

}