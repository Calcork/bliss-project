<?php

namespace App\Controllers\AppController\SystemCall;

class Help extends SystemCall
{
    function notFound() : void
    {
        echo sprintf('The command: %s:%s was not found, please run Help:listSystemcalls to list all registered commands.', $this->section, $this->method);
    }

    function listSystemcalls() : void {

        echo 'The following commands are registered for systemcall aliases:' . PHP_EOL . PHP_EOL;

        foreach($this->app->getSystemCallAliases() as $section => $array) {

            foreach ($array as $method => $command) {

                $str = $section . ':' . $method;
                echo $str . PHP_EOL;

            }

        }

    }

}