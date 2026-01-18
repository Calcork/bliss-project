<?php

namespace Hizech\Bliss\Data;

use ErrorException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class Variable
{

    public const string STRINGY_QUERY_REGEX = '/^(?<name>(?:_){0,1}[a-zA-Z][a-zA-Z0-9_-]*)(?:\((?<type>null|string|bool|int|float)\)|(?<type_empty>))(?<operator>=|%|!|\$)(?<value>.*)$/m';

    /**
     * Ensures a variable is an object of, or derived from, a specific class/interface.
     *
     * @param mixed $var
     * @param string $class_name
     * @return object
     * @throws InvalidArgumentException
     */
    public static function assertObject(mixed $var, string $class_name): object
    {
        if (!is_object($var)) {
            throw new InvalidArgumentException('Variable must be an object.');
        }

        try {
            $target_ref = new ReflectionClass($class_name);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Target class or interface '{$class_name}' does not exist.");
        }

        $var_ref = new ReflectionClass($var);

        $valid =
            ($target_ref->isInterface() && $var_ref->implementsInterface($class_name))
            || $var_ref->isSubclassOf($class_name)
            || $var instanceof $class_name;

        if (!$valid) {
            throw new InvalidArgumentException("Variable must implement or extend {$class_name}");
        }

        return $var;
    }

    public static function castValue(string $type, string $value) : int|null|float|string|bool {
        return match($type) {
            'string' => $value,
            'bool' => match(strtolower($value)) {
                'true', '1' => true,
                'false', '0' => false,
                default => null
            },
            'int' => filter_var($value, FILTER_VALIDATE_INT) !== false
                ? (int) $value
                : null,
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT) !== false
                ? (float) $value
                : null,
            'null' => null,
            default => null
        };
    }

    public static function inverseCastValue(mixed $value): string
    {
        return match (gettype($value)) {
            'boolean' => $value ? 'true' : 'false',
            'integer' => (string) $value,
            'double'  => (string) $value, // PHP calls floats "double"
            'string'  => $value,
            'NULL'    => 'null',
            default   => (string) $value,
        };
    }

    /**
     * Convert a name-value pair back into stringy query format.
     *
     * Automatically handles type detection and proper escaping for string values.
     * String values will have backslashes and quotes escaped, then wrapped in quotes.
     *
     * @param string $name The parameter name
     * @param int|float|null|string|bool $value The value to encode
     * @return string The stringy query representation
     * @throws InvalidArgumentException If value type is unsupported
     */
    public static function inverseCastStringyQuery(string $name, int|float|null|string|bool $value) : string {

        $str = $name;

        if($value === null) $type_string = '(null)';
        elseif(is_string($value)) $type_string = '(string)';
        elseif(is_float($value)) $type_string = '(float)';
        elseif(is_int($value)) $type_string = '(int)';
        elseif(is_bool($value)) $type_string = '(bool)';
        else throw new InvalidArgumentException('Invalid value type.');

        $string_cast = self::inverseCastValue($value);

        // Wrap string values in single quotes with proper escaping
        if(is_string($value)) {
            // Escape backslashes first, then single quotes
            $escaped = str_replace('\\', '\\\\', $string_cast);
            $escaped = str_replace("'", "\\'", $escaped);
            $string_cast = "'" . $escaped . "'";
        }

        $str = $str . $type_string . '=' . $string_cast;

        return $str;

    }

    /**
     * Stringy Query Format
     *
     * A stringy query is a compact string format for encoding typed key-value pairs.
     *
     * Format: name[(type)]operator value
     *
     * Components:
     * - name: [a-zA-Z][a-zA-Z0-9_-]* - alphanumeric identifier starting with letter
     * - type: (string|int|float|bool|null) - optional explicit type
     * - operator:
     *   = : explicit value (requires type if not using shorthand)
     *   % : null value
     *   $ : true boolean
     *   ! : false boolean
     *
     * Type Rules:
     * - string: MUST be wrapped in single quotes ('value')
     *   - Escape literal backslash: \\
     *   - Escape literal quote: \'
     * - int/float/bool/null: no quotes, plain values
     *
     * Examples:
     *   name='John Doe'           -> ['name' => 'John Doe']
     *   count(int)=42             -> ['count' => 42]
     *   price(float)=19.99        -> ['price' => 19.99]
     *   active$                   -> ['active' => true]
     *   disabled!                 -> ['disabled' => false]
     *   deleted%                  -> ['deleted' => null]
     *   path='C:\\Users'          -> ['path' => 'C:\Users']
     *   msg='It\'s working'       -> ['msg' => "It's working"]
     *
     * @param string $query
     * @return array<string, int|null|float|string|bool> ['name' => string $name, 'value' => int|null|float|string|bool $value]
     * @throws ErrorException If query format is invalid or string values lack quotes
     */
    public static function castStringyQuery(string $query) : array {

        $v = preg_match(self::STRINGY_QUERY_REGEX, $query, $matches);

        if ($v === false) {
            throw new ErrorException('Regex execution failed.');
        }
        if ($v === 0) {
            throw new ErrorException("Invalid stringy query format: '{$query}'");
        }

        $name = $matches['name'];
        // PHPStan knows from regex that 'type' always exists; 'type_empty' is for empty type syntax
        $type = ($matches['type'] !== '') ? $matches['type'] : '';

        $operator = $matches['operator'];
        $value = $matches['value'];
        $value = trim($value);

        if ($operator === '%') {
            $type = 'null';
            $value = 'null';
        } elseif($operator === '$') {
            $type = 'bool';
            $value = 'true';
        }
        elseif($operator === '!') {
            $type = 'bool';
            $value = 'false';
        } elseif($type === '') {
            $type = 'string';
        }

        // Handle string types - quotes required only for values with special chars
        if ($type === 'string') {
            if (preg_match("/^'(.*)'$/s", $value, $string_matches)) {
                // Quoted string - extract and unescape
                $value = $string_matches[1];

                // Unescape backslash-escaped characters (\\ and \')
                $value = preg_replace_callback(
                    '/\\\\([\\\\\'"])/',
                    fn($m) => $m[1],
                    $value
                );
            }
            // Unquoted string is allowed if no problematic characters
            // (spaces, quotes, backslashes would have been stripped/mangled by shell anyway)
        }

        $cast_value = self::castValue($type, $value);

        return ['name' => $name, 'value' => $cast_value];

    }

    /**
     * @param array<string, mixed>|callable $values
     */
    // Every slash eats whatever is after it, in the case of a var, a slash would eat the special meaning of var, so it would escape it
    public static function squareReplacePlaceholders(string $str, array|callable $values) : string {

        // Pattern supports: [[variable]] or [[variable|option|option2]]
        // Variable names can include dots for nesting: [[user.name]]
        $pattern = '/(?<escaper>[\\\\]*)\[\[(?<var>[a-zA-Z][a-zA-Z0-9_.-]*?)(?:\|(?<options>[^\]]+))?\]\]/';

        $is_callback = is_callable($values);

        $callback = function($match) use($values, $is_callback) {

            $escaper = $match['escaper'];
            $var = $match['var'];
            $options_str = $match['options'] ?? '';

            // Parse options into array
            $options = $options_str !== '' ? explode('|', $options_str) : [];

            $placeholder_is_var = false;
            $escaper_count = strlen($escaper);
            $escaper_new_count = $escaper_count;

            $escaper_str = '';
            $var_str = '';

            // If even number of backslashes, treat as variable
            if($escaper_count % 2 === 0) {
                $placeholder_is_var = true;
            }
            // If odd number of backslashes, it's escaped
            else {
                $escaper_new_count = $escaper_new_count - 1;
            }

            if($escaper_new_count > 0) {
                // eat half the tokens
                $escaper_new_count = $escaper_new_count / 2;
            }

            $escaper_str = str_repeat('\\', $escaper_new_count);

            if($placeholder_is_var) {
                if($is_callback) {
                    // Call the callback with variable key and options
                    $var_str = $values($var, $options);
                } elseif(isset($values[$var])) {
                    // Use array value (backward compatibility)
                    $var_str = $values[$var];
                } else {
                    // Keep as literal placeholder if value not found
                    $var_str = '[[' . $var . ($options_str !== '' ? '|' . $options_str : '') . ']]';
                }
            } else {
                // Keep as literal placeholder (escaped)
                $var_str = '[[' . $var . ($options_str !== '' ? '|' . $options_str : '') . ']]';
            }

            return $escaper_str . $var_str;

        };

        $result = preg_replace_callback(
            $pattern,
            $callback,
            $str
        );

        return $result;

    }

}