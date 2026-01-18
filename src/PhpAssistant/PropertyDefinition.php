<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Value object describing a property to be generated.
 */
final class PropertyDefinition
{
    /**
     * @param string $name Property name
     * @param string $visibility public, protected, or private
     * @param string|null $type Type declaration
     * @param mixed $default_value Default value (use PropertyDefinition::NO_DEFAULT for no default)
     * @param bool $is_static Whether the property is static
     * @param bool $is_readonly Whether the property is readonly
     * @param string|null $doc_comment Property doc comment
     */
    public function __construct(
        public readonly string $name,
        public readonly string $visibility,
        public readonly ?string $type = null,
        public readonly mixed $default_value = self::NO_DEFAULT,
        public readonly bool $is_static = false,
        public readonly bool $is_readonly = false,
        public readonly ?string $doc_comment = null,
    ) {}

    /**
     * Sentinel value indicating no default value should be set.
     */
    public const NO_DEFAULT = '__NO_DEFAULT__';

    /**
     * Check if this property has a default value.
     */
    public function hasDefaultValue(): bool
    {
        return $this->default_value !== self::NO_DEFAULT;
    }
}
