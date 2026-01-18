<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Value object describing a class to be generated.
 */
final class ClassDefinition
{
    /**
     * @param string $namespace The namespace for the class
     * @param string $class_name The class name
     * @param array<string, string|null> $imports Class imports (FQCN => alias or null)
     * @param array<string, mixed> $constants Constant name => value pairs
     * @param array<PropertyDefinition> $properties Property definitions
     * @param array<MethodDefinition> $methods Method definitions
     * @param string|null $extends Parent class FQCN
     * @param array<string> $implements Interface FQCNs
     * @param bool $is_final Whether the class is final
     * @param bool $is_abstract Whether the class is abstract
     * @param string|null $doc_comment Class-level doc comment
     */
    public function __construct(
        public readonly string $namespace,
        public readonly string $class_name,
        public readonly array $imports = [],
        public readonly array $constants = [],
        public readonly array $properties = [],
        public readonly array $methods = [],
        public readonly ?string $extends = null,
        public readonly array $implements = [],
        public readonly bool $is_final = false,
        public readonly bool $is_abstract = false,
        public readonly ?string $doc_comment = null,
    ) {}

    /**
     * Get the fully qualified class name.
     */
    public function getFullyQualifiedClassName(): string
    {
        return $this->namespace . '\\' . $this->class_name;
    }
}
