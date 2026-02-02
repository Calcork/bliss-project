<?php

namespace App\Lib\TemplateMaster\Twig;

use Twig\Cache\FilesystemCache;

/**
 * Handles file locking issues on Windows when multiple processes
 * write to the same cache file simultaneously.
 */
class WindowsSafeFilesystemCache extends FilesystemCache
{

    private int $max_retries;
    private int $base_delay_ms;

    public function __construct(string $directory, int $options = 0, int $max_retries = 5, int $base_delay_ms = 10)
    {
        parent::__construct($directory, $options);
        $this->max_retries = $max_retries;
        $this->base_delay_ms = $base_delay_ms;
    }

    public function write(string $key, string $content): void
    {
        $dir = \dirname($key);

        if (!is_dir($dir)) {
            if (!$this->safeMkdir($dir, 0777, true)) {
                clearstatcache(true, $dir);
                if (!is_dir($dir)) {
                    throw new \RuntimeException(\sprintf('Unable to create the cache directory (%s).', $dir));
                }
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(\sprintf('Unable to write in the cache directory (%s).', $dir));
        }

        for ($attempt = 0; $attempt < $this->max_retries; $attempt++) {

            $tmp_file = @tempnam($dir, basename($key));

            if ($tmp_file === false) {
                $this->delay($attempt);
                continue;
            }

            if (false !== @file_put_contents($tmp_file, $content)) {

                if ($this->safeRename($tmp_file, $key)) {
                    @chmod($key, 0666 & ~umask());
                    $this->invalidateBytecodeCache($key);
                    return;
                }

                @unlink($tmp_file);

                if (file_exists($key) && @file_get_contents($key) === $content) {
                    return;
                }

            } else {
                @unlink($tmp_file);
            }

            $this->delay($attempt);
        }

        if (file_exists($key)) {
            return;
        }

        throw new \RuntimeException(\sprintf('Failed to write cache file "%s" after %d attempts.', $key, $this->max_retries));
    }

    private function delay(int $attempt): void
    {
        $delay_ms = $this->base_delay_ms * (1 << $attempt) + random_int(0, $this->base_delay_ms);
        usleep($delay_ms * 1000);
    }

    private function safeRename(string $from, string $to): bool
    {
        set_error_handler(function (): bool { return true; });

        try {
            $result = rename($from, $to);
        } finally {
            restore_error_handler();
        }

        return $result;
    }

    private function safeMkdir(string $dir, int $mode, bool $recursive): bool
    {
        set_error_handler(function (): bool { return true; });

        try {
            $result = mkdir($dir, $mode, $recursive);
        } finally {
            restore_error_handler();
        }

        if (!$result && is_dir($dir)) {
            return true;
        }

        return $result;
    }

    private function invalidateBytecodeCache(string $key): void
    {
        if (\function_exists('opcache_invalidate') && filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN)) {
            @opcache_invalidate($key, true);
        }
    }

}
