<?php

namespace App\Lib\TailwindcssManager;

use App\Base\Services\TailwindcssManager as TailwindcssManagerInterface;
use RuntimeException;
use Symfony\Component\Process\Process;

class TailwindcssManager implements TailwindcssManagerInterface
{
    private string $storage_path;

    /** @var array<string, array{output_path: string, watch_paths: list<string>}>|null */
    private ?array $registry = null;

    function __construct(string $storage_path)
    {
        $this->storage_path = $storage_path;
    }

    /**
     * @param list<string> $watch_paths Supports glob patterns (e.g. /app/twig/*.twig)
     */
    function register(string $input_path, string $output_path, array $watch_paths): void
    {
        $registry = $this->getRegistry();

        $new_entry = [
            'output_path' => $output_path,
            'watch_paths' => $watch_paths,
        ];

        if (isset($registry[$input_path]) && $registry[$input_path] === $new_entry) {
            return;
        }

        $registry[$input_path] = $new_entry;
        $this->saveRegistry($registry);
    }

    function build(string $input_path): void
    {
        $entry = $this->getEntry($input_path);

        $process = new Process(['npx', '@tailwindcss/cli', '-i', $input_path, '-o', $entry['output_path']]);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Tailwind CSS build failed: ' . $process->getErrorOutput());
        }

        $this->saveSnapshot($input_path, $entry['watch_paths']);
    }

    function isOutputFresh(string $input_path): bool
    {
        $entry = $this->getEntry($input_path);

        if (!file_exists($entry['output_path'])) {
            return false;
        }

        $snapshot = $this->loadSnapshot($input_path);

        if ($snapshot === null) {
            return false;
        }

        $resolved_paths = $this->resolvePaths([$input_path, ...$entry['watch_paths']]);

        // New file appeared that wasn't in the snapshot
        foreach ($resolved_paths as $path) {
            $current_mtime = filemtime($path);
            $stored_mtime = $snapshot[$path] ?? null;

            if ($stored_mtime === null || $current_mtime !== $stored_mtime) {
                return false;
            }
        }

        // File was removed that was in the snapshot
        foreach ($snapshot as $path => $mtime) {
            if (!file_exists($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{output_path: string, watch_paths: list<string>}
     */
    private function getEntry(string $input_path): array
    {
        $registry = $this->getRegistry();

        if (!isset($registry[$input_path])) {
            throw new RuntimeException("Tailwind input path not registered: $input_path");
        }

        return $registry[$input_path];
    }

    /**
     * Expands glob patterns into real file paths.
     * Non-glob paths are included as-is if they exist.
     *
     * @param list<string> $paths
     * @return list<string>
     */
    private function resolvePaths(array $paths): array
    {
        $resolved = [];

        foreach ($paths as $path) {
            if (str_contains($path, '*') || str_contains($path, '?')) {
                $matches = glob($path, GLOB_BRACE);
                if ($matches !== false) {
                    foreach ($matches as $match) {
                        if (is_file($match)) {
                            $resolved[] = $match;
                        }
                    }
                }
            } elseif (file_exists($path)) {
                $resolved[] = $path;
            }
        }

        return $resolved;
    }

    /**
     * @param list<string> $watch_paths
     */
    private function saveSnapshot(string $input_path, array $watch_paths): void
    {
        $resolved = $this->resolvePaths([$input_path, ...$watch_paths]);

        $mtimes = [];
        foreach ($resolved as $path) {
            $mtimes[$path] = filemtime($path);
        }

        $json = json_encode($mtimes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Failed to encode snapshot to JSON');
        }

        file_put_contents($this->getSnapshotFilePath($input_path), $json);
    }

    /**
     * @return array<string, int>|null
     */
    private function loadSnapshot(string $input_path): ?array
    {
        $file = $this->getSnapshotFilePath($input_path);

        if (!file_exists($file)) {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            return null;
        }

        /** @var array<string, int> $decoded */
        return $decoded;
    }

    private function getSnapshotFilePath(string $input_path): string
    {
        $key = md5($input_path);
        return $this->storage_path . DIRECTORY_SEPARATOR . "snapshot_$key.json";
    }

    private function getRegistryFilePath(): string
    {
        return $this->storage_path . DIRECTORY_SEPARATOR . 'registry.json';
    }

    /**
     * @return array<string, array{output_path: string, watch_paths: list<string>}>
     */
    private function getRegistry(): array
    {
        if ($this->registry !== null) {
            return $this->registry;
        }

        $file = $this->getRegistryFilePath();

        if (!file_exists($file)) {
            $this->registry = [];
            return $this->registry;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            throw new RuntimeException("Failed to read registry file: $file");
        }

        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw new RuntimeException("Invalid registry file: $file");
        }

        /** @var array<string, array{output_path: string, watch_paths: list<string>}> $decoded */
        $this->registry = $decoded;
        return $this->registry;
    }

    /**
     * @param array<string, array{output_path: string, watch_paths: list<string>}> $registry
     */
    private function saveRegistry(array $registry): void
    {
        if (!is_dir($this->storage_path)) {
            mkdir($this->storage_path, 0755, true);
        }

        $json = json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            throw new RuntimeException('Failed to encode registry to JSON');
        }

        file_put_contents($this->getRegistryFilePath(), $json);
        $this->registry = $registry;
    }
}
