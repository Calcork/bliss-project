<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Fluent builder for ClassDefinition.
 */
class ClassDefinitionBuilder
{
    private string $namespace;
    private string $class_name;

    /** @var array<string, string|null> */
    private array $imports = [];

    /** @var array<string, mixed> */
    private array $constants = [];

    /** @var array<PropertyDefinition> */
    private array $properties = [];

    /** @var array<MethodDefinition> */
    private array $methods = [];

    private ?string $extends = null;

    /** @var array<string> */
    private array $implements = [];

    private bool $is_final = false;
    private bool $is_abstract = false;
    private ?string $doc_comment = null;

    public function __construct(string $namespace, string $class_name)
    {
        $this->namespace = $namespace;
        $this->class_name = $class_name;
    }

    /**
     * Add an import statement.
     */
    public function addImport(string $class, ?string $alias = null): self
    {
        $this->imports[$class] = $alias;
        return $this;
    }

    /**
     * Add multiple imports.
     *
     * @param array<string, string|null> $imports
     */
    public function addImports(array $imports): self
    {
        foreach ($imports as $class => $alias) {
            $this->imports[$class] = $alias;
        }
        return $this;
    }

    /**
     * Add a constant.
     */
    public function addConstant(string $name, mixed $value): self
    {
        $this->constants[$name] = $value;
        return $this;
    }

    /**
     * Add a property.
     */
    public function addProperty(PropertyDefinition $property): self
    {
        $this->properties[] = $property;
        return $this;
    }

    /**
     * Add a method.
     */
    public function addMethod(MethodDefinition $method): self
    {
        $this->methods[] = $method;
        return $this;
    }

    /**
     * Set the parent class.
     */
    public function extends(string $class): self
    {
        $this->extends = $class;
        return $this;
    }

    /**
     * Add an implemented interface.
     */
    public function implements(string $interface): self
    {
        $this->implements[] = $interface;
        return $this;
    }

    /**
     * Set multiple implemented interfaces.
     *
     * @param array<string> $interfaces
     */
    public function implementsAll(array $interfaces): self
    {
        $this->implements = array_merge($this->implements, $interfaces);
        return $this;
    }

    /**
     * Mark the class as final.
     */
    public function final(): self
    {
        $this->is_final = true;
        return $this;
    }

    /**
     * Mark the class as abstract.
     */
    public function abstract(): self
    {
        $this->is_abstract = true;
        return $this;
    }

    /**
     * Set the class doc comment.
     */
    public function docComment(string $comment): self
    {
        $this->doc_comment = $comment;
        return $this;
    }

    /**
     * Build the ClassDefinition.
     */
    public function build(): ClassDefinition
    {
        return new ClassDefinition(
            namespace: $this->namespace,
            class_name: $this->class_name,
            imports: $this->imports,
            constants: $this->constants,
            properties: $this->properties,
            methods: $this->methods,
            extends: $this->extends,
            implements: $this->implements,
            is_final: $this->is_final,
            is_abstract: $this->is_abstract,
            doc_comment: $this->doc_comment,
        );
    }
}
