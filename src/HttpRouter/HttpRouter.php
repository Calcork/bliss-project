<?php

namespace Hizech\Bliss\HttpRouter;

use Hizech\Bliss\App\Services\StaticResourceCacheInterface;
use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Matcher\Found;
use Hizech\Bliss\Route\RouteCollection;

/**
 * NOTE - Parameters, on failure to validate, will be treated as though they were never submitted
 * NOTE - Parameters will always be returned as strings, up to the controller to cast them
 */
final class HttpRouter implements \Hizech\Bliss\App\Services\HttpRouter
{
    public const string NAME_SEP = '|';

    /**
     * @var callable(string): string
     */
    private mixed $content_generator;

    /**
     * @param callable(string): string $content_generator
     */
    public function __construct(
        private RouteCollection $routes,
        private StaticResourceCacheInterface $cache,
        private string $cache_key,
        callable $content_generator
    ) {
        $this->content_generator = $content_generator;
    }

    function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    private static function normalizePath(string $path): string
    {
        $path_only = parse_url($path, PHP_URL_PATH) ?? '/';
        $path_only = rawurldecode($path_only);
        return '/' . ltrim($path_only, '/');
    }

    public function dispatch(HttpMethod $method, string $path): Found|null
    {
        $normalized_path = self::normalizePath($path);

        foreach ($this->routes->all() as $name => $route) {
            if (!in_array($method, $route->http_methods, true)) {
                continue;
            }

            try {
                $regex_pattern = $this->cache->getCacheSmart($this->cache_key, $name);
                if (!is_string($regex_pattern) || $regex_pattern === '') {
                    $regex_pattern = ($this->content_generator)($name);
                }
            } catch (\InvalidArgumentException) {
                $regex_pattern = ($this->content_generator)($name);
            }

            if (preg_match($regex_pattern, $normalized_path, $matches)) {
                $params = [];

                foreach ($matches as $key => $value) {
                    if (is_string($key) && !empty($value)) {
                        $params[$key] = $value;
                    }
                }

                return new Found($name, $params);
            }
        }

        return null;
    }
}