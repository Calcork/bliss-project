<?php

namespace Hizech\Bliss\Argv;

use Hizech\Bliss\Data\Variable;
use ErrorException;

class Util
{

    /**
     * Parse argv into command info.
     *
     * Supports two formats:
     * - SystemCall: Section:method "--arg=value"
     * - Callable: Full\Class\Name::method "--arg=value"
     * Args can either be regular args, starting with --  , meant as data, or with !- , meant for the cli itself, arguments you dont want spoiling the data for controllers.
     * @param array<int, string> $argv
     * @return array{
     *      section: string|null,
     *      method: string|null,
     *      arguments: array<string, mixed>,
     *      special_arguments: array<string, mixed>,
     *      callable: string|null
     *  }
     * @throws ErrorException
     */
    public static function analyzeArgv(array $argv): array
    {

        if (count($argv) < 2) {
            throw new ErrorException('No system call section:method provided.');
        }

        $command = $argv[1];

        $arguments = [];
        $special_arguments = [];

        for ($i = 2; $i < count($argv); $i++) {

            $token = $argv[$i];

            if(str_starts_with($token, '--')) {

                $new_token = substr(trim($token), 2);

                $ar = Variable::castStringyQuery($new_token);

                if(str_starts_with($ar['name'], '_')) $special_arguments[$ar['name']] = $ar['value'];
                else $arguments[$ar['name']] = $ar['value'];

            }

            else {
                throw new ErrorException('Invalid argument provided.');
            }

        }

        $parts = explode(':', $command, 2);

        if (count($parts) === 2) {
            $callable = null;
            $section = $parts[0];
            $method = $parts[1];
        }

        else {
            $callable = $command;
            $section = null;
            $method = null;
        }

        return [

            'section' => $section,
            'method' => $method,
            'arguments' => $arguments,
            'special_arguments' => $special_arguments,
            'callable' => $callable,

        ];

    }

}