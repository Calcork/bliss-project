<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Generates PHP files that return arrays.
 */
class ArrayFileGenerator
{
    public function __construct(
        private CodeFormatter $formatter,
    ) {}

    /**
     * Generate a PHP file that returns an array.
     *
     * @param array<mixed> $data The array data to export
     * @param string|null $header Custom file header (null for default)
     */
    public function generate(array $data, ?string $header = null): string
    {
        $sections = [];

        // File header
        if ($header !== null) {
            $sections[] = $header;
        } else {
            $sections[] = $this->formatter->formatFileHeader();
        }

        // Declare strict types
        $sections[] = "declare(strict_types=1);\n";

        // Return statement
        $formatted_array = $this->formatter->formatArray($data);
        $sections[] = 'return ' . $formatted_array . ';';

        return implode("\n", $sections);
    }

    /**
     * Generate a PHP file that returns an array with a specific variable assignment.
     *
     * @param string $variable_name Variable name (without $)
     * @param array<mixed> $data The array data
     * @param string|null $header Custom file header
     */
    public function generateWithVariable(string $variable_name, array $data, ?string $header = null): string
    {
        $sections = [];

        // File header
        if ($header !== null) {
            $sections[] = $header;
        } else {
            $sections[] = $this->formatter->formatFileHeader();
        }

        // Declare strict types
        $sections[] = "declare(strict_types=1);\n";

        // Variable assignment
        $formatted_array = $this->formatter->formatArray($data);
        $sections[] = '$' . $variable_name . ' = ' . $formatted_array . ';';

        return implode("\n", $sections);
    }
}
