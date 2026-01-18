<?php

namespace Hizech\Bliss\JsonData;

use ErrorException;

class JsonData
{

    /**
     * @return array<string, mixed>
     */
    static function fromFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new ErrorException('JsonData file not found: ' . $path);
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new ErrorException('Invalid or non-array JSON structure in: ' . $path);
        }

        return $data;
    }

    /**
     * Write new data, if exists overwrite it
     * @param array<string, mixed> $data
     */
    static function writeData(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new ErrorException('Failed to encode data to JSON: ' . json_last_error_msg());
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new ErrorException('Failed to create directory: ' . $dir);
            }
        }

        $tmp_path = $path . '.tmp';
        if (file_put_contents($tmp_path, $json) === false) {
            throw new ErrorException('Failed to write temporary file: ' . $tmp_path);
        }

        if (!rename($tmp_path, $path)) {
            throw new ErrorException('Failed to replace original file: ' . $path);
        }
    }

}