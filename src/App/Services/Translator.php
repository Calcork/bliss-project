<?php

namespace Hizech\Bliss\App\Services;

interface Translator
{
    /**
     * @param array<string, mixed> $params
     */
    public function trans(string $key, array $params, string $locale): string;
}