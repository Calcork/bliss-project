<?php

namespace Hizech\Bliss\App;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\ORMSetup;
use Hizech\Bliss\App\HookFulfillers\Http\OnAfterResponseDecided;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestResponse;
use Hizech\Bliss\App\HookFulfillers\Http\OnNotFound;
use Hizech\Bliss\App\Services\HttpRouter as HttpRouterInterface;
use Hizech\Bliss\App\Services\Logger as LoggerInterface;
use Hizech\Bliss\App\Services\Translator as TranslatorInterface;
use Hizech\Bliss\App\Services\StaticResourceCacheInterface;
use Hizech\Bliss\Cache\StaticResourceCache\StaticResourceCache;
use Hizech\Bliss\Cache\StorageAdapter\Case\FileStorageAdapter;
use Hizech\Bliss\Cache\TrackerAdapter\Case\FileTrackerAdapter;
use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\Controller\SystemcallControllerReport;
use Hizech\Bliss\DoctrineWrapper\DoctrineWrapper;
use Hizech\Bliss\Env\Env;
use Hizech\Bliss\HttpRouter\HttpRouter;
use Hizech\Bliss\Logger\SimpleLogger;
use Hizech\Bliss\Misc\Util;
use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Matcher\Found;
use Hizech\Bliss\Route\RouteCollection;
use Hizech\Bliss\Translator\SimpleTranslator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * Services are extendible, and recognized by getServices. Basically services are objects, used to construct and conduct logic.
 */
abstract class App
{

    /**
     * @var array<string, bool|int|float|null|string>|null
     */
    private ?array $env;

    private bool $error_handling_initialized;
    private RouteCollection $routes;
    /**
     * @var array<string, array<string, ControllerHandler>> $systemcall_aliases
     */
    private array $systemcall_aliases;
    /**
     * @var array<string, array<string, \Hizech\Bliss\Controller\ControllerHandler>> $direct_controller_aliases
     */
    private array $direct_controller_aliases;

    private string $root_path;

    final public function __construct(

        private ?LoggerInterface $logger = null,
        private ?TranslatorInterface $translator = null,
        private ?HttpRouterInterface $http_router = null,
        private ?DoctrineWrapper $doctrine = null,

    ) {

        $this->root_path = realpath(__DIR__ . '/../../');
        $this->env = null;
        $this->error_handling_initialized = false;

    }

    protected function createRoutes() : RouteCollection {
        return require($this->getRoutesPath());
    }

    /**
     * @return array<string, array<string, ControllerHandler>>
     */
    protected function createSystemcallAliases() : array
    {
        return require($this->getSystemCallAliasesPath());
    }

    final public function getRoutes() : RouteCollection {

        if(isset($this->routes)) return $this->routes;
        else {
            $this->routes = $this->createRoutes();
            return $this->routes;
        }

    }

    /**
     * @return \Hizech\Bliss\Controller\ControllerHandler[][]
     */
    final public function getSystemCallAliases() : array {

        if(isset($this->systemcall_aliases)) return $this->systemcall_aliases;
        else {
            $this->systemcall_aliases = $this->createSystemcallAliases();
            return $this->systemcall_aliases;
        }

    }

    /**
     * @return array<int, string>
     */
    public function getStorageFolders(): array
    {
        return [

            'logs/caught-errors',
            'logs/errors',
            'translator/cache',
            'http-router/cache',
            'doctrine-orm',

        ];

    }

    public function purgeCache() : void {
        foreach ($this->getStorageCacheFolders() as $folder) {
            $path = Util::joinPath($this->getStoragePath(), $folder);
            if (!is_dir($path)) {
                continue;
            }
            $files = glob($path . DIRECTORY_SEPARATOR . '*');
            if ($files === false) {
                continue;
            }
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                } elseif (is_dir($file)) {
                    $sub_files = glob($file . DIRECTORY_SEPARATOR . '*');
                    if ($sub_files === false) {
                        continue;
                    }
                    foreach ($sub_files as $sub_file) {
                        if (is_file($sub_file)) {
                            unlink($sub_file);
                        }
                    }
                }
            }
        }
    }

    public function rebuildCache() : void {
        $this->purgeCache();
        $this->translator = null;
        $this->http_router = null;
        $this->getTranslator();
        $this->getHttpRouter();
    }

    /**
     * @return array<int, string>
     */
    public function getStorageCacheFolders(): array
    {

        return [
            'translator/cache',
            'http-router/cache',
        ];

    }

    /**
     * @return array<string, mixed>
     */
    final public static function loadEnv(string $env_path): array
    {
        return Env::fileToEnv($env_path);
    }

    final public function getRootPath(): string
    {
        return $this->root_path;
    }

    private function getRoutesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/http_routes.php');
    }

    private function getSystemCallAliasesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/systemcall_aliases.php');
    }

    private function getDirectControllerAliasesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/direct-controller-aliases.php');
    }

    private function getTranslationsPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/translations');
    }

    final public function getStoragePath(): string
    {
        $env = $this->getEnv();
        return $env['STORAGE_DIR'] ?? $this->root_path . Util::pathByParts('/storage');
    }
    
    final public function getEnvPath(): string
    {
        return $this->getRootPath() . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * @return array<string, bool|int|float|string|null>
     */
    final public function getEnv(): array
    {
        if ($this->env === null) {
            $this->env = self::loadEnv($this->getEnvPath());
        }
        return $this->env;
    }

    final public static function getUri(Request $request) : string {
        return $request->getPathInfo();
    }

    final public function createStaticResourceCacheUtility(string $cache_dir): StaticResourceCacheInterface
    {

        $env = $this->getEnv();
        $hot_reload = ($env['APP_DEVELOPMENT'] ?? false) === true;

        $storage = new FileStorageAdapter($cache_dir . DIRECTORY_SEPARATOR . 'storage');
        $tracker = new FileTrackerAdapter($cache_dir . DIRECTORY_SEPARATOR . 'tracker');

        return new StaticResourceCache($hot_reload, $storage, $tracker);

    }

    final public function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $env = $this->getEnv();
            $timezone = new \DateTimeZone($env['APP_TIMEZONE'] ?? 'UTC');
            $this->logger = new SimpleLogger(
                Util::joinPath($this->getStoragePath(), 'logs'),
                $timezone
            );
        }
        return $this->logger;
    }

    /**
     * @return list<string>
     */
    private function getAllTranslationLocales(): array
    {
        $path = $this->getTranslationsPath();
        if (!is_dir($path)) {
            return [];
        }

        $dirs = glob($path . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
        if ($dirs === false) {
            return [];
        }

        return array_map(fn($d) => basename($d), $dirs);
    }

    private function loadTranslationFile(string $locale): string
    {
        $locale_dir = Util::joinPath($this->getTranslationsPath(), $locale);

        if (!is_dir($locale_dir)) {
            return '[]';
        }

        $files = glob($locale_dir . DIRECTORY_SEPARATOR . '*.yaml');
        if ($files === false || $files === []) {
            return '[]';
        }

        $merged = [];

        foreach ($files as $file) {
            $namespace = basename($file, '.yaml');
            $parsed = Yaml::parseFile($file);

            if (!is_array($parsed)) {
                continue;
            }

            foreach ($parsed as $key => $value) {
                $merged[$namespace . '.' . $key] = $value;
            }
        }

        return json_encode($merged);
    }

    private function getTranslationFileMtime(string $locale): string
    {
        $locale_dir = Util::joinPath($this->getTranslationsPath(), $locale);

        if (!is_dir($locale_dir)) {
            return '0';
        }

        $files = glob($locale_dir . DIRECTORY_SEPARATOR . '*.yaml');
        if ($files === false || $files === []) {
            return '0';
        }

        $max_mtime = 0;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if ($mtime !== false && $mtime > $max_mtime) {
                $max_mtime = $mtime;
            }
        }

        return (string) $max_mtime;
    }

    /**
     * @return list<string>
     */
    private function getAllRouteNames(): array
    {
        return array_keys($this->getRoutes()->all());
    }

    private function getRouteRegex(string $route_name): string
    {
        $route = $this->getRoutes()->all()[$route_name] ?? null;

        if ($route === null) {
            return '';
        }

        return $route->toRegex();
    }

    private function getRoutesFileMtime(string $route_name): string
    {
        $routes_file = $this->getRoutesPath();
        return file_exists($routes_file) ? (string) filemtime($routes_file) : '0';
    }

    final public function getTranslator(): TranslatorInterface
    {
        if ($this->translator === null) {

            $cache = $this->createStaticResourceCacheUtility(
                Util::joinPath($this->getStoragePath(), 'translator', 'cache')
            );

            $cache->registerResource(
                'translations',
                fn(?string $instance) => $instance === null
                    ? $this->getAllTranslationLocales()
                    : $this->loadTranslationFile($instance),
                fn(string $locale) => $this->getTranslationFileMtime($locale),
                fn(string $item, string $locale, string $stored_meta) =>
                    $stored_meta === $this->getTranslationFileMtime($locale)
            );

            $cached_translations = $cache->getCacheSmart('translations');
            $decoded_cache = null;

            if (is_array($cached_translations)) {
                $decoded_cache = [];
                foreach ($cached_translations as $locale => $json) {
                    $parsed = json_decode($json, true);
                    $decoded_cache[$locale] = is_array($parsed) ? $parsed : [];
                }
            }

            $this->translator = new SimpleTranslator(
                function (string $key, string $locale): ?string {
                    $json = $this->loadTranslationFile($locale);
                    $parsed = json_decode($json, true);
                    if (!is_array($parsed)) {
                        return null;
                    }
                    return $parsed[$key] ?? null;
                },
                $decoded_cache
            );
        }
        return $this->translator;
    }

    final public function getHttpRouter(): HttpRouterInterface
    {
        if ($this->http_router === null) {

            $cache = $this->createStaticResourceCacheUtility(
                Util::joinPath($this->getStoragePath(), 'http-router', 'cache')
            );

            $cache->registerResource(
                'route-regex',
                fn(?string $instance) => $instance === null
                    ? $this->getAllRouteNames()
                    : $this->getRouteRegex($instance),
                fn(string $route_name) => $this->getRoutesFileMtime($route_name),
                fn(string $item, string $route_name, string $stored_meta) =>
                    $stored_meta === $this->getRoutesFileMtime($route_name)
            );

            $cached_regex = $cache->getCacheSmart('route-regex');

            $this->http_router = new HttpRouter(
                $this->getRoutes(),
                is_array($cached_regex) ? $cached_regex : null
            );
        }
        return $this->http_router;
    }

    final public function getDoctrine() :  DoctrineWrapper
    {

        if ($this->doctrine === null) {

            $config = ORMSetup::createAttributeMetadataConfig(

                paths: [$this->getRootPath() . '/app/models'],
                isDevMode: $this->getEnv()['APP_DEVELOPMENT'],
                cache: null

            );

            $config->setProxyDir($this->getEnv()['STORAGE_DIR'] . '/doctrine-orm/proxy');
            $config->setProxyNamespace('App\\Doctrine\\Proxies');

            $connection_conf = null;

            if ($this->getEnv()['DB_DRIVER'] === 'sqlite') {

                $path = ($this->getEnv()['DB_NAME']  === ':memory:') ? ':memory:' : Util::joinPath($this->getRootPath(), 'sqlite', $this->getEnv()['DB_NAME']);

                $connection_conf = [

                    'driver' => 'pdo_sqlite',
                    'path' => $path,

                ];

            } elseif ($this->getEnv()['DB_DRIVER']  === 'mysql') {

                $connection_conf = [

                    'dbname' => $this->getEnv()['DB_NAME'],
                    'user' => $this->getEnv()['DB_USER'],
                    'password' => $this->getEnv()['DB_PASS'],
                    'host' => $this->getEnv()['DB_HOST'],
                    'driver' => 'pdo_mysql',

                ];

            } else {
                throw new \ErrorException('Currently only db drivers supported are mysql and sqlite.');
            }

            $connection = DriverManager::getConnection($connection_conf, $config);

            $this->doctrine = new DoctrineWrapper($connection, $config);
        }

        return $this->doctrine;

    }

    final public function doErrorHandling() : void
    {
        if ($this->error_handling_initialized) {
            return;
        }

        $env = $this->getEnv();
        $is_dev = ($env['APP_DEVELOPMENT'] ?? false) === true;

        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) use ($is_dev) {
            $this->getLogger()->log('errors', new \ErrorException($errstr, 0, $errno, $errfile, $errline));
            if ($is_dev) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
            return true;
        });

        set_exception_handler(function (\Throwable $e) use ($is_dev) {
            $this->getLogger()->log('errors', $e);
            if ($is_dev) {
                throw $e;
            }
        });

        $this->error_handling_initialized = true;
    }

    /**
     * @return array<string, array<string, \Hizech\Bliss\Controller\ControllerHandler>>
     */
    private function createDirectControllerAliases() : array {
        return require($this->getDirectControllerAliasesPath());
    }

    /**
     * @return array<string, array<string, \Hizech\Bliss\Controller\ControllerHandler>>
     */
    final public function getDirectControllerAliases(): array
    {
        if(isset($this->direct_controller_aliases)) {
            return $this->direct_controller_aliases;
        }
        else {
            $this->direct_controller_aliases = $this->createDirectControllerAliases();
            return $this->direct_controller_aliases;
        }
    }

    protected function onBeforeFulfillerExecuted(string $name, object $fulfiller) : void {}

    /**
     * @return array<string, OnContestResponse>
     */
    protected function onHttpContestResponseFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, OnAfterResponseDecided>
     */
    protected function onHttpAfterResponseDecidedFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, OnNotFound>
     */
    protected function onHttpNotFoundFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, OnContestContext>
     */
    protected function onHttpContestContextFulfillers() : array {
        return [];
    }

    /**
     * @param Request $request
     * @param callable(Response $response) : void |null $when_response_decided
     * @return Response|null
     * @throws \ErrorException
     */
    final public function runHttp(Request $request,  ?callable $when_response_decided = null) : ?Response {

        if($when_response_decided === null) {

            $when_response_decided = function(Response $response) : void{
                $response->send();
            };

        }

        // Hook
        foreach($this->onHttpContestContextFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestContext($request);

            if ($r instanceof Response) {
                $r->send();
                return null;
            }

            $request = $r;

        }

        $uri = App::getUri($request);
        $http_method = HttpMethod::fromName($request->getMethod());

        $routing_result = $this->getHttpRouter()->dispatch($http_method, $uri);

        $controller_handler = null;

        if ($routing_result instanceof Found) {

            $route = $this->getRoutes()->all()[$routing_result->route];

            $controller_handler = $route->controller_handler;

        }

        elseif($routing_result === null) {

            // Hook
            foreach($this->onHttpNotFoundFulfillers() as $name => $fulfiller) {

                $this->onBeforeFulfillerExecuted($name, $fulfiller);

                $r = $fulfiller->onNotFound($request);

                if($r === true) continue;
                elseif($r instanceof Response) {
                    $r->send();
                    return null;
                }
                elseif($r instanceof  ControllerHandler) {
                    $controller_handler = $r;
                    break;
                }
                else {
                    throw new \LogicException('Impossible.');
                }

            }

        }

        else {
            throw new \ErrorException('Unknown http routing result.');
        }

        $controller = new ($controller_handler->class)($this);

        /**
         * @var \Symfony\Component\HttpFoundation\Response $response
         */
        $response = $controller->{$controller_handler->method}($request, $routing_result);

        // Hook
        foreach($this->onHttpContestResponseFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestResponse($response);

            $response = $r;

        }

        $when_response_decided($response);

        // Hook
        foreach($this->onHttpAfterResponseDecidedFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $fulfiller->OnAfterResponseDecided($response);

        }

        return $response;

    }

    /**
     * @param bool $echo
     * @param callable $callable
     * @param array<string, float|int|bool|string|null> $arguments
     * @return void
     */
    final public function runCallable(bool $echo, callable $callable, array $arguments = []) : void {


        if ($echo) echo ($callable)(...$arguments);
        else ($callable)(...$arguments);

    }

    /**
     * @return array<string, \Hizech\Bliss\App\HookFulfillers\Systemcall\OnAfterControllerRun>
     */
    protected function onSystemcallAfterControllerRunFulfillers() : array {
        return [];
    }


    /**
     * @return array<string, \Hizech\Bliss\App\HookFulfillers\Systemcall\OnNotFound>
     */
    protected function onSystemcallNotFoundFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, \Hizech\Bliss\App\HookFulfillers\Systemcall\OnContestContext>
     */
    protected function onSystemcallContestContextFulfillers() : array {
        return [];
    }

    /**
     * @param array<string, float|int|bool|string|null> $arguments
     * @param array<string, float|int|bool|string|null> $attributes
     */
    final public function runSystemcall(string $section, string $method, array $arguments, array $attributes = []) : ?SystemcallControllerReport {

        // Hook
        foreach($this->onSystemcallContestContextFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestContext($section, $method, $arguments, $attributes);

            if($r === false) return null;

            $section = $r['section'];
            $method = $r['method'];
            $arguments = $r['arguments'];
            $attributes = $r['attributes'];

        }

        // Handle system call type
        $aliases = $this->getSystemCallAliases();

        $controller_handler = null;

        // If found
        if(isset($aliases[$section][$method])) {

            $controller_handler = $aliases[$section][$method];

        }

        else {

            // Hook
            foreach($this->onSystemcallNotFoundFulfillers() as $name => $fulfiller) {

                $this->onBeforeFulfillerExecuted($name, $fulfiller);

                $r = $fulfiller->onNotFound($section, $method, $arguments, $attributes);

                if($r === true) {
                    continue;
                }
                elseif($r instanceof ControllerHandler) {
                    $controller_handler = $r;
                    break;
                }
                else {
                    throw new \LogicException('Impossible.');
                }

            }

        }

        $controller = new ($controller_handler->class)($this);

        try {
            $result = $controller->{$controller_handler->method}($section, $method, $arguments, $attributes);
        }
        catch (\Throwable $throwable) {
            $result = SystemcallControllerReport::thrown($throwable);
        }

        // Hook
        foreach($this->onSystemcallAfterControllerRunFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $fulfiller->onAfterControllerRun($result);

        }

        return $result;

    }

}
