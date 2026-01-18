<?php

namespace Hizech\Bliss\HtmlElement;

use Hizech\Bliss\Html\HtmlInput;

class ChildlessElement implements Resolveable
{

    /**
     * @param array<string, string|null> $attributes
     */
    function __construct(
        public readonly string $tag,
        public readonly array $attributes
    ) {}

    function resolve() : string {
        return '<' . $this->tag . ' ' . HtmlInput::buildAttrs($this->attributes) . ' />';
    }

}
