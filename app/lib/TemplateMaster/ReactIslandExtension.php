<?php

namespace App\Lib\TemplateMaster;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class ReactIslandExtension extends AbstractExtension
{

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('react_island', $this->reactIsland(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param array<string, mixed> $props
     */
    public function reactIsland(string $name, array $props = []): string
    {
        $attrs = 'data-react="' . htmlspecialchars($name, ENT_QUOTES) . '"';

        if ($props !== []) {
            $attrs .= " data-props='" . htmlspecialchars(json_encode($props, JSON_THROW_ON_ERROR), ENT_QUOTES) . "'";
        }

        return '<div ' . $attrs . '></div>';
    }

}
