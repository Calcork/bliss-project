<?php

namespace App\Lib\TemplateMaster;

use Hizech\Bliss\App\Services\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslationExtension extends AbstractExtension
{

    public function __construct(
        private Translator $translator,
        private string $default_locale = 'en',
    ) {}

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('trans', $this->trans(...)),
        ];
    }

    public function setDefaultLocale(string $locale): void
    {
        $this->default_locale = $locale;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function trans(string $key, array $params = [], ?string $locale = null): string
    {
        return $this->translator->trans($key, $params, $locale ?? $this->default_locale);
    }

}
