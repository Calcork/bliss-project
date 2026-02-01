<?php

namespace Hizech\Bliss\Translator;

interface TranslatorFetcher
{
    function getTranslation(string $locale, string $key): ?string;
}/**
     * @param array<string, array<string, ControllerHandler>> $systemcall_aliases
     * @return array<string, array<string, ControllerHandler>>
     */
    function onContestSystemcallAliases(App $app, array $systemcall_aliases): array
    {
        return array_merge($systemcall_aliases, []);
    }
