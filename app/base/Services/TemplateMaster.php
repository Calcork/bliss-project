<?php

namespace App\Base\Services;

interface TemplateMaster
{
    /**
     * @param string $name
     * @param callable() : string $twig_syntax_fallback
     * @return void
     */
    public function loadAutoTemplate(string $name, callable $twig_syntax_fallback) : void;
    /**
     * @param array<string, mixed> $context
     */
    public function twigCustomRender(string $path, array $context): string;

}