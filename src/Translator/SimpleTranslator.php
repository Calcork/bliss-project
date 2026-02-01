<?php

namespace Hizech\Bliss\Translator;

use Hizech\Bliss\App\Services\Translator;
use Hizech\Bliss\Data\Variable;

class SimpleTranslator implements Translator
{

    function __construct(

        /**
         * @var callable(string $key, string $locale) : ?string
         */
        private $translator_fetcher,
        /**
         * @var null|array<string, array<string, string>> $cache_locales_translations
         */
        private ?array $cache_locales_translations = null,

    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public function trans(string $key, array $params, string $locale): string
    {

        if(isset($this->cache_locales_translations[$locale][$key])) {
            $translation = $this->cache_locales_translations[$locale][$key];
        }

        else{
            $translation = ($this->translator_fetcher)($key, $locale);
        }

        if(!isset($translation)) return '[[' . $key . ']]';

        $value = Variable::squareReplacePlaceholders($translation, $params);

        return $value;

    }
}
