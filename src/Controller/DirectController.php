<?php

namespace Hizech\Bliss\Controller;

interface DirectController
{

    /**
     * @param array<string, float|bool|int|null|string> $args
     */
    function __construct(array $args);

}