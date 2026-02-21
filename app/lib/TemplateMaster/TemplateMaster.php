<?php

namespace App\Lib\TemplateMaster;

use App\Lib\TemplateMaster\Twig\BetterTwig;
use App\Lib\TemplateMaster\Twig\FakeTemplateLoader;
use Twig\Loader\ArrayLoader;
use Twig\Template;

class TemplateMaster implements \App\Base\Services\TemplateMaster
{

    /** @var array<string, true> */
    private array $loaded_templates = [];

    /** @var array<string, array{path: string, cls: string}>|null */
    private ?array $cache_report = null;

    public function __construct(
        private BetterTwig $twig,
        private ArrayLoader $array_loader,
        private FakeTemplateLoader $fake_loader,
        private string $cache_reporter_path,
        private bool $is_development,
    ) {}

    public function getTwig(): BetterTwig
    {
        return $this->twig;
    }

    /**
     * @param callable() : string $twig_syntax_fallback
     */
    public function loadAutoTemplate(string $name, callable $twig_syntax_fallback): void
    {
        if (isset($this->loaded_templates[$name])) {
            return;
        }

        $this->loaded_templates[$name] = true;

        // In dev mode, always run the callable fresh
        if ($this->is_development) {
            $this->array_loader->setTemplate($name, $twig_syntax_fallback());
            return;
        }

        // Production: try loading from compiled cache
        $report = $this->loadCacheReport();

        if (isset($report[$name]) && file_exists($report[$name]['path'])) {
            require_once $report[$name]['path'];

            /** @var Template $template */
            $template = new $report[$name]['cls']($this->twig);

            $this->fake_loader->add($name);
            $this->twig->addTemplate($name, $template);
            return;
        }

        // Not cached yet — render via ArrayLoader, then register for caching
        $this->array_loader->setTemplate($name, $twig_syntax_fallback());

        $cls = $this->twig->getTemplateClass($name);
        $cache = $this->twig->getCache();

        if ($cache === false) {
            return;
        }

        $path = $cache->generateKey($name, $cls);
        $this->registerAutoTemplateCaching($name, $cls, $path);
    }

    /**
     * @return array<string, array{path: string, cls: string}>
     */
    private function loadCacheReport(): array
    {
        if ($this->cache_report !== null) {
            return $this->cache_report;
        }

        if (!file_exists($this->cache_reporter_path)) {
            $this->cache_report = [];
            return $this->cache_report;
        }

        /** @var array<string, array{path: string, cls: string}> $loaded */
        $loaded = require $this->cache_reporter_path;
        $this->cache_report = $loaded;
        return $this->cache_report;
    }

    private function registerAutoTemplateCaching(string $name, string $cls, string $path): void
    {
        $this->cache_report ??= [];
        $this->cache_report[$name] = ['path' => $path, 'cls' => $cls];

        file_put_contents(
            $this->cache_reporter_path,
            '<?php return ' . var_export($this->cache_report, true) . ';'
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function twigCustomRender(string $path, array $context = []): string
    {
        return $this->twig->render($path, $context);
    }

}
