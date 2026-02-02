<?php

namespace App\Lib\TemplateMaster\Twig;

use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\Template;

class BetterTwig extends Environment
{

    /** @var array<string, Template> */
    private array $templates = [];

    /**
     * @param array<string, mixed> $options
     */
    function __construct(LoaderInterface $loader, array $options = [])
    {
        parent::__construct($loader, $options);
    }

    function addTemplate(string $name, Template $template): void
    {
        $this->templates[$name] = $template;
    }

    public function loadTemplate(string $cls, ?string $name, ?int $index = null): Template
    {
        $key = $name ?? $cls;

        if (isset($this->templates[$key])) {
            return $this->templates[$key];
        }

        return parent::loadTemplate($cls, $name, $index);
    }

}
