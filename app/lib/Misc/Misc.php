<?php

namespace App\Lib\Misc;

class Misc
{
    /**
     * @return string[]
     */
    static function getRecursiveFilesInDir(string $dir) : array {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    static function getClassFromFile(string $file) : string {
        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new \RuntimeException("Cannot read file: {$file}");
        }

        $namespace = '';
        $class = '';

        if (preg_match('/namespace\s+([^;]+);/', $contents, $match)) {
            $namespace = $match[1];
        }

        if (preg_match('/class\s+(\w+)/', $contents, $match)) {
            $class = $match[1];
        }

        if ($class === '') {
            throw new \RuntimeException("No class found in file: {$file}");
        }

        return $namespace !== '' ? $namespace . '\\' . $class : $class;
    }


    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    static function stringifyEnv(array $data) : array
    {
        $env = [];
        foreach ($data as $key => $value) {
            $env[$key] = is_string($value) ? $value : (string) json_encode($value);
        }
        return $env;
    }


}

