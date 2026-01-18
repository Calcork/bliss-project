<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Handles code formatting for PHP file generation.
 */
class CodeFormatter
{
    private const INDENT = '    ';

    /**
     * Indent a block of code by a given level.
     */
    public function indent(string $code, int $level = 1): string
    {
        if ($code === '') {
            return '';
        }

        $indent = str_repeat(self::INDENT, $level);
        $lines = explode("\n", $code);

        return implode("\n", array_map(
            fn(string $line) => $line === '' ? '' : $indent . $line,
            $lines
        ));
    }

    /**
     * Format an array as PHP code.
     *
     * @param array<mixed> $data
     */
    public function formatArray(array $data, int $indent_level = 0): string
    {
        if (empty($data)) {
            return '[]';
        }

        $is_list = array_is_list($data);
        $indent = str_repeat(self::INDENT, $indent_level);
        $inner_indent = str_repeat(self::INDENT, $indent_level + 1);

        $lines = ['['];

        foreach ($data as $key => $value) {
            $formatted_value = $this->formatValue($value, $indent_level + 1);

            if ($is_list) {
                $lines[] = $inner_indent . $formatted_value . ',';
            } else {
                $formatted_key = $this->formatArrayKey($key);
                $lines[] = $inner_indent . $formatted_key . ' => ' . $formatted_value . ',';
            }
        }

        $lines[] = $indent . ']';

        return implode("\n", $lines);
    }

    /**
     * Format a value for PHP code output.
     */
    public function formatValue(mixed $value, int $indent_level = 0): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            // Check if it's a class reference (starts with backslash)
            if (str_starts_with($value, '\\') && !str_contains($value, ' ')) {
                return $value . '::class';
            }

            return $this->formatString($value);
        }

        if (is_array($value)) {
            return $this->formatArray($value, $indent_level);
        }

        return var_export($value, true);
    }

    /**
     * Format a string with proper escaping.
     */
    public function formatString(string $value): string
    {
        // Use single quotes unless the string contains single quotes
        if (!str_contains($value, "'")) {
            return "'" . $value . "'";
        }

        // Use double quotes if string contains single quotes but not double
        if (!str_contains($value, '"')) {
            return '"' . addcslashes($value, "\\\$\"\n\r\t") . '"';
        }

        // Fall back to escaped single quotes
        return "'" . str_replace("'", "\\'", $value) . "'";
    }

    /**
     * Format an array key.
     */
    public function formatArrayKey(string|int $key): string
    {
        if (is_int($key)) {
            return (string) $key;
        }

        return $this->formatString($key);
    }

    /**
     * Format a class constant definition.
     */
    public function formatConstant(string $name, mixed $value, string $visibility = 'public'): string
    {
        $formatted_value = $this->formatValue($value, 1);

        // Handle multiline arrays
        if (is_array($value) && !empty($value)) {
            return $visibility . ' const ' . $name . ' = ' . $formatted_value . ';';
        }

        return $visibility . ' const ' . $name . ' = ' . $formatted_value . ';';
    }

    /**
     * Format a use/import statement.
     */
    public function formatImport(string $class, ?string $alias = null): string
    {
        $statement = 'use ' . ltrim($class, '\\');

        if ($alias !== null) {
            $statement .= ' as ' . $alias;
        }

        return $statement . ';';
    }

    /**
     * Format a method signature.
     *
     * @param array<string, string> $parameters Parameter name => type pairs
     */
    public function formatMethodSignature(
        string $name,
        string $visibility,
        array $parameters,
        ?string $return_type,
        bool $is_static = false
    ): string {
        $parts = [$visibility];

        if ($is_static) {
            $parts[] = 'static';
        }

        $parts[] = 'function';
        $parts[] = $name;

        $param_strings = [];
        foreach ($parameters as $param_name => $param_type) {
            if ($param_type !== '') {
                $param_strings[] = $param_type . ' $' . $param_name;
            } else {
                $param_strings[] = '$' . $param_name;
            }
        }

        $signature = implode(' ', $parts) . '(' . implode(', ', $param_strings) . ')';

        if ($return_type !== null) {
            $signature .= ': ' . $return_type;
        }

        return $signature;
    }

    /**
     * Wrap code in a method body with braces.
     */
    public function wrapMethodBody(string $signature, string $body): string
    {
        $lines = [$signature];
        $lines[] = '{';

        if ($body !== '') {
            $lines[] = $this->indent($body);
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * Generate a file header comment.
     */
    public function formatFileHeader(?string $custom_message = null): string
    {
        $lines = [
            '<?php',
            '',
            '/**',
            ' * AUTO-GENERATED FILE - DO NOT EDIT MANUALLY',
            ' * Generated at: ' . date('Y-m-d H:i:s'),
        ];

        if ($custom_message !== null) {
            $lines[] = ' *';
            $lines[] = ' * ' . $custom_message;
        }

        $lines[] = ' */';
        $lines[] = '';

        return implode("\n", $lines);
    }
}
