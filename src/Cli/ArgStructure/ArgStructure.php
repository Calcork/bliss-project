<?php

namespace Hizech\Bliss\Cli\ArgStructure;

use Hizech\Bliss\Cli\ArgStructure\Input\Enum;
use Hizech\Bliss\Cli\ArgStructure\Input\Flag;
use Hizech\Bliss\Cli\ArgStructure\Input\Path;
use Hizech\Bliss\Cli\ArgStructure\Input\Variable;
use ErrorException;

class ArgStructure
{
    /** @var array<string, array{name: string, required: bool, input: mixed}> */
    private array $args = [];

    public function addArg(string $name, bool $is_required, mixed $input) : void
    {
        $this->args[$name] = [
            'name' => $name,
            'required' => $is_required,
            'input' => $input
        ];
    }

    static function verifyVariableName(string $name): string {
        if(preg_match("/^[a-z][a-z-]*$/", $name)) return $name;
        else throw new ErrorException('Bad name');
    }

    static function verifyVariableValue(string $value): string {
        if(preg_match("/^[^\s]+$/", $value)) return $value;
        else throw new ErrorException('Bad value');
    }

    function inputToRegex(string $name) : string {

        $input = $this->args[$name]['input'];

        $sanitized_name = self::verifyVariableName($name);

        $pattern = null;

        if($input instanceof Enum) {
            $value_regex = implode('|', $input->getValues());
            $pattern = '--' . $sanitized_name . sprintf('=(?<%s>' . $value_regex . ')', $name);
        }
        elseif($input instanceof Flag) {
            $value_regex = 'true|false';
            $pattern = '--' . $sanitized_name . sprintf('=(?<%s>' . $value_regex . ')', $name);
        }
        elseif($input instanceof Path) {

            $parts = [];

            if ($input->allow_absolute) $parts[] = '\/[^\s]*';
            if ($input->allow_relative) $parts[] = '(?:\.\/|[^\/\s])[^\s]*';
            if(!$input->allow_absolute && !$input->allow_relative) throw new ErrorException('Bad path structure');


            $value_regex = implode('|', $parts);

            $pattern = '--' . $sanitized_name . sprintf('=(?<%s>' . $value_regex . ')', $name);
        }
        elseif ($input instanceof Variable) {
            $value_regex = $input->regex ?? '[^\s]+';
            $pattern = '--' . $sanitized_name . sprintf('=(?<%s>%s)', $name, $value_regex);
        }
        else {
            throw new ErrorException('Bad input');
        }

        return $pattern;

    }

    public function toRegex() : string {

        $str = '';

        $first = true;

        foreach ($this->args as $name => $arg) {

            $current_regex = $this->inputToRegex($name);

            if($first) {
                $str .= $current_regex;
                $first = false;
            }
            else $str .= '\s+' . $current_regex;

        }

        return $str;

    }

    /**
     * @return array<string, string>
     */
    public function parseQueryString(string $query): array
    {
        $result = [];

        foreach ($this->args as $name => $meta) {

            $regex = '/--' . $name . '=([^\s]+)/';
            if (preg_match($regex, $query, $matches)) {
                $value = $matches[1];

                // Validate against full regex built for this arg
                $full_pattern = '/^' . $this->inputToRegex($name) . '$/';
                $arg_string   = '--' . $name . '=' . $value;

                if (preg_match($full_pattern, $arg_string) !== 1) {
                    throw new ErrorException('Invalid value for argument: ' . $name);
                }

                $result[$name] = $value;
            } else {
                if ($meta['required']) {
                    throw new ErrorException('Missing required argument: ' . $name);
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function buildQueryFromValues(array $values): ?string
    {
        $str = '';
        $first = true;

        foreach ($this->args as $name => $meta) {

            $is_required = $meta['required'];
            $has_value   = array_key_exists($name, $values);

            if ($is_required && !$has_value) {
                // required argument missing → invalid query
                return null;
            }

            if ($has_value) {
                $value = $values[$name];
                if (!is_string($value)) return null;

                $current_string = '--' . $name . '=' . $value;

                $regex = $this->inputToRegex($name);

                // Validate given input against its own args logic generated regex
                if (preg_match('/^' . $regex . '$/', $current_string) !== 1) return null;

                if ($first) {
                    $str .= $current_string;
                    $first = false;
                } else {
                    $str .= ' ' . $current_string;
                }
            }
        }

        return $str;
    }

}
