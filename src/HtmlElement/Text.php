<?php

namespace Hizech\Bliss\HtmlElement;

class Text implements Resolveable
{

    function __construct(
        public readonly string $content,
    ) {
    }

    function resolve () : string
    {
        return $this->content;
    }

}