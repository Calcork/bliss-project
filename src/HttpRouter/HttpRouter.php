<?php

namespace Hizech\Bliss\HttpRouter;

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

    public function __construct(
        private RouteCollection $routes,
        /**
         * @var array<string, string>|null
         */
        private ?array $cache_regex = null,
    ) {
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

            if(isset($this->cache_regex)) $regex_pattern = $this->cache_regex[$name];
            else $regex_pattern = $route->toRegex();

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