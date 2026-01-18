<?php

namespace Hizech\Bliss\HttpRouter;

use Hizech\Bliss\Cache\StaticResourceCache;
use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Matcher\Found;
use Hizech\Bliss\Route\RouteCollection;

/**
 * NOTE - Parameters, on failure to validate, will be treated as though they were never submitted
 * NOTE - Parameters will always be returned as strings, up to the controller to cast them
 */
final class HttpRouter implements \Hizech\Bliss\App\Services\HttpRouter
{

    public CONST string NAME_SEP = '|';
    public function __construct(

        private RouteCollection $routes,
        private StaticResourceCache $cache,
        private string $cache_key = 'route-regex',

    ){}

    function getRoutes() : RouteCollection
    {
        return $this->routes;
    }

    static private function normalizePath(string $path): string
    {
        // Drop ?query and #fragment, keep only the path part.
        $path_only = parse_url($path, PHP_URL_PATH) ?? '/';

        // url decode - IMPORTANT
        $path_only = rawurldecode($path_only);

        // Ensure a single leading slash; don't touch trailing slash.
        return '/' . ltrim($path_only, '/');
    }

    public function dispatch(HttpMethod $method, string $path): Found | null
    {

        $normalized_path = self::normalizePath($path);

        foreach ($this->routes->all() as $name => $route) {

            // Check allowed method
            if (!in_array($method, $route->http_methods, true)) {
                continue;
            }

            // Regex pattern
            try {
                $regex_pattern = $this->cache->get($this->cache_key, $name);
            } catch (\InvalidArgumentException) {
                $regex_pattern = '';
            }

            if ($regex_pattern === '') {
                $regex_pattern = $this->routes->all()[$name]->toRegex();
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