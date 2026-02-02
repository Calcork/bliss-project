<?php

namespace App\Base\Services;

interface TailwindcssManager
{
    /**
     * @param list<string> $watch_paths
     */
    function register(string $input_path, string $output_path, array $watch_paths) : void;
    function build(string $input_path) : void;
    function isOutputFresh(string $input_path) : bool;
}