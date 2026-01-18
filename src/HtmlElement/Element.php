<?php

namespace Hizech\Bliss\HtmlElement;

use Hizech\Bliss\Html\HtmlInput;

class Element implements Resolveable
{

    /** @var array<Resolveable> */
    private array $inner_objects;

    /**
     * @param array<string, string|null> $attributes
     */
    function __construct(
        public readonly string $tag,
        public readonly array $attributes
    ) {
        $this->inner_objects = [];
    }

    function pushInner(Resolveable $object) : void {
        $this->inner_objects[] = $object;
    }

    function resolve() : string {

        $s = '<' . $this->tag . ' ' . HtmlInput::buildAttrs($this->attributes) . '>';

        foreach ($this->inner_objects as $object) {
            $s .= $object->resolve();
        }

        $s .= '</' . $this->tag . '>';

        return $s;

    }

}
