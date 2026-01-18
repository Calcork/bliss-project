<?php

namespace Hizech\Bliss\App;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\ORMSetup;
use Hizech\Bliss\App\HookFulfillers\Http\OnAfterResponseSent;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestContext;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestController;
use Hizech\Bliss\App\HookFulfillers\Http\OnContestResponse;
use Hizech\Bliss\App\HookFulfillers\Http\OnNotFound;
use Hizech\Bliss\App\HookFulfillers\OnContestHttpRoutes;
use Hizech\Bliss\App\HookFulfillers\OnContestSystemcallAliases;
use Hizech\Bliss\App\Services\DbEntityManager as DbEntityManagerInterface;
use Hizech\Bliss\App\Services\HttpRouter as HttpRouterInterface;
use Hizech\Bliss\App\Services\Logger as LoggerInterface;
use Hizech\Bliss\App\Services\Translator as TranslatorInterface;
use Hizech\Bliss\Cache\StaticResourceCache;
use Hizech\Bliss\Controller\ControllerHandler;
use Hizech\Bliss\DoctrineWrapper\DoctrineWrapper;
use Hizech\Bliss\Env\Env;
use Hizech\Bliss\HttpRouter\HttpRouter;
use Hizech\Bliss\Logger\SimpleLogger;
use Hizech\Bliss\Misc\Util;
use Hizech\Bliss\Route\HttpMethod;
use Hizech\Bliss\Route\Matcher\Found;
use Hizech\Bliss\Route\RouteCollection;
use Hizech\Bliss\Translator\SimpleTranslator;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
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
     * @var array<string, string>
     */
    private ?array $registered_services;
    /**
     * @var array<string, StaticResourceCache>
     */
    private array $cache_instances = [];

    final public function __construct(

        private string $root_path,

        private ?LoggerInterface $logger = null,
        private ?TranslatorInterface $translator = null,
        private ?HttpRouterInterface $http_router = null,
        private ?DbEntityManagerInterface $db_entity_manager = null,

    ) {

        $this->env = null;
        $this->error_handling_initialized = false;
        $this->registered_services = null;
        $this->routes = new RouteCollection();
        $this->systemcall_aliases = [];

        $routes = require($this->getRoutesPath());

        $fulfillers = $this->onContestHttpRoutesFulfillers();
        
        // Hook
        foreach($fulfillers as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);
            $routes = $fulfiller->onContestHttpRoutes($this, $routes);

        }

        $this->routes = $routes;

        $systemcall_aliases = require($this->getSystemCallAliasesPath());

        // Hook
        foreach($this->onContestSystemcallAliasesFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $systemcall_aliases = $fulfiller->onContestSystemcallAliases($this, $systemcall_aliases);

        }

        $this->systemcall_aliases = $systemcall_aliases;

    }


    /**
     * @return array<string, OnContestHttpRoutes>
     */
    protected function onContestHttpRoutesFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, OnContestSystemcallAliases>
     */
    protected function onContestSystemcallAliasesFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, string>
     */
    final public function getRegisteredServices() : array {

        if(isset($this->registered_services)) {
            return $this->registered_services;
        }

        else {

            $registered_services = $this->defineRegisteredServices();

            $this->registered_services = $registered_services;
            return $this->registered_services;

        }

    }

    /**
     * @return string[]
     */
    protected function defineRegisteredServices() : array {

        return [

            'logger' => 'getLogger',
            'translator' => 'getTranslator',
            'http-router' => 'getHttpRouter',
            'db-entity-manager' => 'getDbEntityManager',

        ];

    }

    /**
     * @return array<int, string>
     */
    final public function getStorageFolders(): array
    {
        return [

            'logs/caught-errors',
            'logs/errors',
            'translator/cache',
            'http-router/cache',
            'doctrine-orm',

        ];

    }

    /**
     * @return array<int, string>
     */
    final public function getStorageCacheFolders(): array
    {

        return [
            'translator/cache',
            'http-router/cache',
        ];

    }

    /**
     * Get the default env directory based on project naming convention.
     * Looks for a sibling folder with -env suffix.
     * e.g., 'bliss' project has env at '../bliss-env/'
     */
    final public static function getDefaultEnvDir(string $root_path): string
    {
        $dirname = basename(rtrim($root_path, DIRECTORY_SEPARATOR));
        $parent = dirname($root_path);
        return Util::joinPath($parent, $dirname . '-env');
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

    final public function getRoutesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/http_routes.php');
    }

    final public function getSystemCallAliasesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/systemcall_aliases.php');
    }

    final public function getDirectControllerAliasesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/direct-controller-aliases.php');
    }

    final public function getTemplatesPath(): string
    {
        return $this->root_path . Util::pathByParts('/app', '/twig');
    }

    final public function getTranslationsPath(): string
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
        return Util::joinPath(self::getDefaultEnvDir($this->root_path), '.env-app');
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

    final public function createStaticResourceCache(string $namespace, string $cache_dir): StaticResourceCache
    {
        $env = $this->getEnv();
        $hot_reload = ($env['APP_DEVELOPMENT'] ?? false) === true;

        $adapter = new FilesystemAdapter($namespace, 0, $cache_dir);

        return new StaticResourceCache($adapter, $hot_reload);
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

    private function loadTranslationFile(string $locale): string
    {
        $file_path = Util::joinPath($this->getTranslationsPath(), $locale . '.yaml');

        if (!file_exists($file_path)) {
            return '[]';
        }

        $translations = Yaml::parseFile($file_path);
        return json_encode($translations);
    }

    private function getTranslationFileMtime(string $locale): int
    {
        $file_path = Util::joinPath($this->getTranslationsPath(), $locale . '.yaml');

        if (!file_exists($file_path)) {
            return 0;
        }

        return filemtime($file_path);
    }

    private function getRouteRegex(string $route_name): string
    {
        $route = $this->getRoutes()->all()[$route_name] ?? null;

        if ($route === null) {
            return '';
        }

        return $route->toRegex();
    }

    private function getRoutesFileMtime(string $route_name): int
    {
        $routes_file = $this->getRoutesPath();
        return file_exists($routes_file) ? filemtime($routes_file) : 0;
    }

    final public function getTranslator(): TranslatorInterface
    {
        if ($this->translator === null) {

            $cache = $this->createStaticResourceCache(
                'translations',
                Util::joinPath($this->getStoragePath(), 'translator', 'cache')
            );

            $cache->register(
                'translations',
                fn(string $locale) => $this->loadTranslationFile($locale),
                fn(string $locale) => $this->getTranslationFileMtime($locale)
            );

            $this->cache_instances['translator'] = $cache;
            $this->translator = new SimpleTranslator($cache, 'translations');
        }
        return $this->translator;
    }

    final public function getHttpRouter(): HttpRouterInterface
    {
        if ($this->http_router === null) {

            $cache = $this->createStaticResourceCache(
                'http-routes',
                Util::joinPath($this->getStoragePath(), 'http-router', 'cache')
            );

            $cache->register(
                'route-regex',
                fn(string $route_name) => $this->getRouteRegex($route_name),
                fn(string $route_name) => $this->getRoutesFileMtime($route_name)
            );

            $this->cache_instances['http-router'] = $cache;
            $this->http_router = new HttpRouter($this->getRoutes(), $cache, 'route-regex');

        }
        return $this->http_router;
    }

    final public function getDbEntityManager() :  DbEntityManagerInterface
    {

        if ($this->db_entity_manager === null) {

            $config = ORMSetup::createAttributeMetadataConfig(

                paths: [$this->getRootPath() . '/app/Models'],
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

            $entityManager = new DoctrineWrapper($connection, $config);

            return $entityManager;
        }

        else return $this->db_entity_manager;

    }

    /**
     * @return array<string, StaticResourceCache>
     */
    final public function getCacheServiceableServices(): array
    {
        // Initialize services to ensure cache instances are created
        $this->getTranslator();
        $this->getHttpRouter();

        return $this->cache_instances;
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


    final public function getRoutes(): RouteCollection
    {
        return $this->routes;
    }

    /**
     * @return array<string, array<string, \Hizech\Bliss\Controller\ControllerHandler>>
     */
    final public function getSystemCallAliases(): array
    {
        return $this->systemcall_aliases;
    }

    /**
     * @return array<string, array<string, \Hizech\Bliss\Controller\ControllerHandler>>
     */
    final public function getDirectControllerAliases(): array
    {
        return require($this->getDirectControllerAliasesPath());
    }

    protected function onBeforeFulfillerExecuted(string $name, object $fulfiller) : void {}

    /**
     * @return array<string, OnContestController>
     */
    protected function onHttpContestControllerFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, OnContestResponse>
     */
    protected function onHttpContestResponseFulfillers() : array {
        return [];
    }

    /**
     * @return array<string, OnAfterResponseSent>
     */
    protected function onHttpAfterResponseSentFulfillers() : array {
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

    final public function runHttp(Request $request) : void {

        // Iteration stub
        $r = null;

        // Hook
        foreach($this->onHttpContestContextFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestContext($this, $request);

            $request = $r;

        }

        $uri = App::getUri($request);
        $http_method = HttpMethod::fromName($request->getMethod());

        $routing_result = $this->getHttpRouter()->dispatch($http_method, $uri);

        $controller_handler = null;

        if ($routing_result instanceof Found) {

            $route = $this->getHttpRouter()->getRoutes()->all()[$routing_result->route];

            $controller_handler = $route->controller_handler;

        }

        elseif($routing_result === null) {

            // Hook
            foreach($this->onHttpNotFoundFulfillers() as $name => $fulfiller) {

                $this->onBeforeFulfillerExecuted($name, $fulfiller);

                $r = $fulfiller->onNotFound($this, $request);

                $controller_handler = $r;

            }

            if(!$controller_handler instanceof ControllerHandler) return;

        }

        else {
            throw new \ErrorException('Unknown http routing result.');
        }

        /**
         * @var \Hizech\Bliss\Controller\HttpController $controller
         */
        $controller = new ($controller_handler->class)($this, $request, $routing_result);

        // Hook
        foreach($this->onHttpContestControllerFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestController($this, $controller);

            $controller = $r;

        }

        /**
         * @var \Symfony\Component\HttpFoundation\Response $response
         */
        $response = $controller->{$controller_handler->method}();

        // Hook
        foreach($this->onHttpContestResponseFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestResponse($this, $response);

            $response = $r;

        }

        $response->send();

        // Hook
        foreach($this->onHttpAfterResponseSentFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $fulfiller->OnAfterResponseSent($this, $response);

        }

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
     * @return array<string, \Hizech\Bliss\App\HookFulfillers\Systemcall\OnContestController>
     */
    protected function onSystemcallContestControllerFulfillers() : array {
        return [];
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
     * @return void
     */
    final public function runSystemcall(string $section, string $method, array $arguments) : void {

        // Iteration stub
        $r = null;

        // Hook
        foreach($this->onSystemcallContestContextFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $new_context = $fulfiller->onContestContext($this, $section, $method, $arguments);
            $section = $new_context['section'];
            $method = $new_context['method'];
            $arguments = $new_context['arguments'];

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

                $r = $fulfiller->onNotFound($this, $section, $method, $arguments);

                $controller_handler = $r;

            }

            if(!$controller_handler instanceof ControllerHandler) return;

        }

        /**
         * @var \Hizech\Bliss\Controller\SystemcallController $controller
         */
        $controller = new ($controller_handler->class)($this, $section, $method, $arguments);

        // Hook
        foreach($this->onSystemcallContestControllerFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $r = $fulfiller->onContestController($this, $controller);

            $controller = $r;

        }

        $controller->{$controller_handler->method}();

        // Hook
        foreach($this->onSystemcallAfterControllerRunFulfillers() as $name => $fulfiller) {

            $this->onBeforeFulfillerExecuted($name, $fulfiller);

            $fulfiller->onAfterControllerRun($this);

        }

    }

}
