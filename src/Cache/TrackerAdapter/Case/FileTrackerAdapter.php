<?php

namespace Hizech\Bliss\Cache\TrackerAdapter\Case;

use Hizech\Bliss\Cache\TrackerAdapter\TrackerAdapter;

class FileTrackerAdapter implements TrackerAdapter
{
    public function __construct(
        private string $cache_dir
    ) {}

    public function saveMetaData(string $item, string $instance, string $content): bool
    {
        $data = $this->loadFile($item);
        $data[$instance] = $content;
        return $this->saveFile($item, $data);
    }

    public function getMetaData(string $item, string $instance): ?string
    {
        $data = $this->loadFile($item);
        return $data[$instance] ?? null;
    }

    public function deleteMetaData(string $item, string $instance): bool
    {
        $data = $this->loadFile($item);
        unset($data[$instance]);
        return $this->saveFile($item, $data);
    }

    /**
     * @return array<string, string>
     */
    private function loadFile(string $item): array
    {
        $path = $this->buildPath($item);

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $data = unserialize($content);
        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, string> $data
     */
    private function saveFile(string $item, array $data): bool
    {
        $path = $this->buildPath($item);

        if (!is_dir($this->cache_dir) && !mkdir($this->cache_dir, 0755, true)) {
            return false;
        }

        return file_put_contents($path, serialize($data)) !== false;
    }

    private function buildPath(string $item): string
    {
        return $this->cache_dir . DIRECTORY_SEPARATOR . $this->sanitize($item) . '.meta';
    }

    private function sanitize(string $value): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $value) ?? $value;
    }
}
