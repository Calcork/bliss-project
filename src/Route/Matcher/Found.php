<?php
declare(strict_types=1);

namespace Hizech\Bliss\Route\Matcher;

final class Found
{
    public function __construct(
        public readonly string $route,
        /** @var array<string,string> */
        public readonly array $params
    ) {}
}