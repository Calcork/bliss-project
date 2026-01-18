<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Generates PHP class file content from ClassDefinition.
 */
class ClassGenerator
{
    public function __construct(
        private CodeFormatter $formatter,
    ) {}

    /**
     * Generate complete class file content.
     */
    public function generate(ClassDefinition $definition, ?string $header = null): string
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

        // Namespace
        $sections[] = 'namespace ' . $definition->namespace . ";\n";

        // Imports
        if (!empty($definition->imports)) {
            $imports = $this->generateImports($definition->imports);
            $sections[] = $imports . "\n";
        }

        // Class definition
        $sections[] = $this->generateClassBody($definition);

        return implode("\n", $sections);
    }

    /**
     * Generate import statements.
     *
     * @param array<string, string|null> $imports
     */
    private function generateImports(array $imports): string
    {
        $lines = [];

        foreach ($imports as $class => $alias) {
            $lines[] = $this->formatter->formatImport($class, $alias);
        }

        return implode("\n", $lines);
    }

    /**
     * Generate the class body.
     */
    private function generateClassBody(ClassDefinition $definition): string
    {
        $lines = [];

        // Doc comment
        if ($definition->doc_comment !== null) {
            $lines[] = $definition->doc_comment;
        }

        // Class declaration line
        $declaration = $this->generateClassDeclaration($definition);
        $lines[] = $declaration;
        $lines[] = '{';

        $body_parts = [];

        // Constants
        if (!empty($definition->constants)) {
            $body_parts[] = $this->generateConstants($definition->constants);
        }

        // Properties
        if (!empty($definition->properties)) {
            $body_parts[] = $this->generateProperties($definition->properties);
        }

        // Methods
        if (!empty($definition->methods)) {
            $body_parts[] = $this->generateMethods($definition->methods);
        }

        if (!empty($body_parts)) {
            $lines[] = $this->formatter->indent(implode("\n\n", $body_parts));
        }

        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * Generate the class declaration line.
     */
    private function generateClassDeclaration(ClassDefinition $definition): string
    {
        $parts = [];

        if ($definition->is_final) {
            $parts[] = 'final';
        }

        if ($definition->is_abstract) {
            $parts[] = 'abstract';
        }

        $parts[] = 'class';
        $parts[] = $definition->class_name;

        if ($definition->extends !== null) {
            $parts[] = 'extends';
            $parts[] = $this->shortenClassName($definition->extends, $definition->imports);
        }

        if (!empty($definition->implements)) {
            $parts[] = 'implements';
            $interfaces = array_map(
                fn(string $interface) => $this->shortenClassName($interface, $definition->imports),
                $definition->implements
            );
            $parts[] = implode(', ', $interfaces);
        }

        return implode(' ', $parts);
    }

    /**
     * Shorten a FQCN if it's in the imports.
     *
     * @param array<string, string|null> $imports
     */
    private function shortenClassName(string $fqcn, array $imports): string
    {
        $fqcn = ltrim($fqcn, '\\');

        // Check if class is imported
        foreach ($imports as $imported_class => $alias) {
            $imported_class = ltrim($imported_class, '\\');

            if ($imported_class === $fqcn) {
                return $alias ?? basename(str_replace('\\', '/', $fqcn));
            }
        }

        // Return with leading backslash for non-imported classes
        return '\\' . $fqcn;
    }

    /**
     * Generate constant definitions.
     *
     * @param array<string, mixed> $constants
     */
    private function generateConstants(array $constants): string
    {
        $lines = [];

        foreach ($constants as $name => $value) {
            $lines[] = $this->formatter->formatConstant($name, $value);
        }

        return implode("\n\n", $lines);
    }

    /**
     * Generate property definitions.
     *
     * @param array<PropertyDefinition> $properties
     */
    private function generateProperties(array $properties): string
    {
        $lines = [];

        foreach ($properties as $property) {
            $lines[] = $this->generateProperty($property);
        }

        return implode("\n\n", $lines);
    }

    /**
     * Generate a single property definition.
     */
    private function generateProperty(PropertyDefinition $property): string
    {
        $parts = [];

        // Doc comment
        if ($property->doc_comment !== null) {
            $parts[] = $property->doc_comment;
        }

        // Property declaration
        $declaration = $property->visibility;

        if ($property->is_static) {
            $declaration .= ' static';
        }

        if ($property->is_readonly) {
            $declaration .= ' readonly';
        }

        if ($property->type !== null) {
            $declaration .= ' ' . $property->type;
        }

        $declaration .= ' $' . $property->name;

        if ($property->hasDefaultValue()) {
            $declaration .= ' = ' . $this->formatter->formatValue($property->default_value);
        }

        $declaration .= ';';
        $parts[] = $declaration;

        return implode("\n", $parts);
    }

    /**
     * Generate method definitions.
     *
     * @param array<MethodDefinition> $methods
     */
    private function generateMethods(array $methods): string
    {
        $method_strings = [];

        foreach ($methods as $method) {
            $method_strings[] = $this->generateMethod($method);
        }

        return implode("\n\n", $method_strings);
    }

    /**
     * Generate a single method definition.
     */
    private function generateMethod(MethodDefinition $method): string
    {
        $parts = [];

        // Doc comment
        if ($method->doc_comment !== null) {
            $parts[] = $method->doc_comment;
        }

        // Method signature
        $signature = $this->formatter->formatMethodSignature(
            $method->name,
            $method->visibility,
            $method->parameters,
            $method->return_type,
            $method->is_static
        );

        // Method body
        $parts[] = $this->formatter->wrapMethodBody($signature, $method->body);

        return implode("\n", $parts);
    }
}
