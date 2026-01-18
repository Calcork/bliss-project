<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Main facade for PHP code generation operations.
 *
 * PhpAssistant provides a clean API for generating PHP code files,
 * including classes and array-based configuration files.
 */
class PhpAssistant
{
    private ClassGenerator $class_generator;
    private ArrayFileGenerator $array_generator;
    private CodeFormatter $formatter;

    public function __construct(
        ?ClassGenerator $class_generator = null,
        ?ArrayFileGenerator $array_generator = null,
        ?CodeFormatter $formatter = null,
    ) {
        $this->formatter = $formatter ?? new CodeFormatter();
        $this->class_generator = $class_generator ?? new ClassGenerator($this->formatter);
        $this->array_generator = $array_generator ?? new ArrayFileGenerator($this->formatter);
    }

    /**
     * Generate a PHP class file content.
     *
     * @param ClassDefinition $definition The class definition
     * @param string|null $header Custom file header (null for default)
     * @return string The generated PHP code
     */
    public function generateClass(ClassDefinition $definition, ?string $header = null): string
    {
        return $this->class_generator->generate($definition, $header);
    }

    /**
     * Generate a PHP file that returns an array.
     *
     * @param array<mixed> $data The array data to export
     * @param string|null $header Custom file header (null for default)
     * @return string The generated PHP code
     */
    public function generateArrayFile(array $data, ?string $header = null): string
    {
        return $this->array_generator->generate($data, $header);
    }

    /**
     * Generate a PHP file with a variable assignment.
     *
     * @param string $variable_name Variable name (without $)
     * @param array<mixed> $data The array data
     * @param string|null $header Custom file header
     * @return string The generated PHP code
     */
    public function generateArrayFileWithVariable(string $variable_name, array $data, ?string $header = null): string
    {
        return $this->array_generator->generateWithVariable($variable_name, $data, $header);
    }

    /**
     * Write generated content to a file.
     *
     * @param string $path The file path to write to
     * @param string $content The content to write
     * @throws \RuntimeException If the file cannot be written
     */
    public function writeFile(string $path, string $content): void
    {
        $directory = dirname($path);

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                throw new \RuntimeException("Failed to create directory: {$directory}");
            }
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            throw new \RuntimeException("Failed to write file: {$path}");
        }
    }

    /**
     * Check if a generated file needs to be regenerated.
     *
     * Compares the new content with existing file content,
     * ignoring the timestamp in the header.
     *
     * @param string $path The file path to check
     * @param string $new_content The new content to compare
     * @return bool True if the file needs regeneration
     */
    public function needsRegeneration(string $path, string $new_content): bool
    {
        if (!file_exists($path)) {
            return true;
        }

        $existing_content = file_get_contents($path);

        if ($existing_content === false) {
            return true;
        }

        // Remove timestamps from both contents for comparison
        $normalize = function (string $content): string {
            // Remove "Generated at: YYYY-MM-DD HH:MM:SS" lines
            return preg_replace(
                '/\* Generated at: \d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',
                '* Generated at: [TIMESTAMP]',
                $content
            ) ?? $content;
        };

        return $normalize($existing_content) !== $normalize($new_content);
    }

    /**
     * Get the code formatter for direct use.
     */
    public function getFormatter(): CodeFormatter
    {
        return $this->formatter;
    }

    /**
     * Create a ClassDefinition builder for fluent API.
     */
    public function classDefinition(string $namespace, string $class_name): ClassDefinitionBuilder
    {
        return new ClassDefinitionBuilder($namespace, $class_name);
    }
}
