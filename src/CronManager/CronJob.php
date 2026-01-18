<?php

namespace Hizech\Bliss\CronManager;

readonly class CronJob
{
    function __construct(

        public string $description,
        /**
         * @var array<int, string>
         */
        public array $command,

    ) {}

}