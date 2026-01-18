<?php

declare(strict_types=1);

namespace Hizech\Bliss\PhpAssistant;

/**
 * Value object describing a method to be generated.
 */
final class MethodDefinition
{
    /**
     * @param string $name Method name
     * @param string $visibility public, protected, or private
     * @param array<string, string> $parameters Parameter name => type pairs
     * @param string|null $return_type Return type declaration
     * @param string $body Method body (without braces)
     * @param bool $is_static Whether the method is static
     * @param string|null $doc_comment Method doc comment
     */
    public function __construct(
        public readonly string $name,
        public readonly string $visibility,
        public readonly array $parameters,
        public readonly ?string $return_type,
        public readonly string $body,
        public readonly bool $is_static = false,
        public readonly ?string $doc_comment = null,
    ) {}
}
