<?php

namespace Hizech\Bliss\App\Services;

interface TemplateMaster
{
    public function loadAutoTemplate(string $name, callable $html_fallback) : void;
    /**
     * @param array<string, mixed> $context
     */
    public function twigCustomRender(string $path, array $context): string;

}