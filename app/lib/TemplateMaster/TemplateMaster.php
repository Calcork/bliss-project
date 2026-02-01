<?php

namespace App\Lib\TemplateMaster;

use Twig\Environment;

class TemplateMaster extends Environment implements \App\Base\Services\TemplateMaster
{

    /**
     * @param string $name
     * @param callable() : string $twig_syntax_fallback
     * @return void
     */
    public function loadAutoTemplate(string $name, callable $twig_syntax_fallback): void
    {
        $loader = $this->getLoader();

        if ($loader->exists($name)) {
            return;
        }

        if ($loader instanceof \Twig\Loader\ChainLoader) {
            foreach ($loader->getLoaders() as $inner) {
                if ($inner instanceof \Twig\Loader\ArrayLoader) {
                    $inner->setTemplate($name, $twig_syntax_fallback());
                    return;
                }
            }
        }

        if ($loader instanceof \Twig\Loader\ArrayLoader) {
            $loader->setTemplate($name, $twig_syntax_fallback());
        }
    }

    /**
     * @param string $path
     * @param array<string, mixed> $context
     * @return string
     */
    public function twigCustomRender(string $path, array $context): string
    {
        return $this->render($path, $context);
    }
}