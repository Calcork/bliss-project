<?php

namespace Hizech\Bliss\Translator;

use Hizech\Bliss\App\Services\Translator;
use Hizech\Bliss\Cache\StaticResourceCache;
use Hizech\Bliss\Data\Variable;

class SimpleTranslator implements Translator
{

    public function __construct(
        private StaticResourceCache $cache,
        private string $cache_key = 'translations'
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public function trans(string $key, array $params, string $locale): string
    {

        try {
            $json = $this->cache->get($this->cache_key, $locale);
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
