<?php

namespace App\Lib\TemplateMaster\Twig;

use Twig\Loader\LoaderInterface;
use Twig\Source;

class FakeTemplateLoader implements LoaderInterface
{

    /** @var array<string, true> */
    private array $templates = [];

    public function add(string $name): void
    {
        $this->templates[$name] = true;
    }

    public function exists(string $name): bool
    {
        return isset($this->templates[$name]);
    }

    public function getSourceContext(string $name): Source
    {
        if (!$this->exists($name)) {
            throw new \Twig\Error\LoaderError("Template '$name' not found in FakeTemplateLoader.");
        }

        return new Source('', $name);
    }

    public function getCacheKey(string $name): string
    {
        if (!$this->exists($name)) {
            throw new \Twig\Error\LoaderError("Template '$name' not found.");
        }

        return $name;
    }

    public function isFresh(string $name, int $time): bool
    {
        return true;
    }

}
