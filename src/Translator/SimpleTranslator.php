<?php

namespace Hizech\Bliss\Translator;

use Hizech\Bliss\App\Services\StaticResourceCacheInterface;
use Hizech\Bliss\App\Services\Translator;
use Hizech\Bliss\Data\Variable;

class SimpleTranslator implements Translator
{
    /**
     * @var callable(string): string
     */
    private mixed $content_generator;

    /**
     * @param callable(string): string $content_generator
     */
    public function __construct(
        private StaticResourceCacheInterface $cache,
        private string $cache_key,
        callable $content_generator
    ) {
        $this->content_generator = $content_generator;
    }

    /**
     * @param array<string, mixed> $params
     */
    public function trans(string $key, array $params, string $locale): string
    {
        try {
            $json = $this->cache->getCacheSmart($this->cache_key, $locale);
            if (!is_string($json) || $json === '') {
                $json = ($this->content_generator)($locale);
            }
            $value = json_decode($json, true);
        } catch (\InvalidArgumentException) {
            return $key;
        }

        if (!is_array($value)) {
            return $key;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $key;
            }
            $value = $value[$segment];
        }

        if (!is_string($value)) {
            return $key;
        }

        $value = Variable::squareReplacePlaceholders($value, $params);

        return $value;
    }
}
