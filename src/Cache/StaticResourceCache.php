<?php

namespace Hizech\Bliss\Cache;

use Psr\Cache\CacheItemPoolInterface;

class StaticResourceCache
{

    /**
     * @var array<string, array{generator: callable(string): string, invalidation_key: null|callable(string): mixed}>
     */
    private array $registered_items = [];

    /**
     * @var array<string, array<int, string>>
     */
    private array $instance_tracker = [];

    public function __construct(
        private CacheItemPoolInterface $adapter,
        private bool $hot_reload = false
    ) {}

    /**
     * @param callable(string): string $generator
     * @param null|callable(string): mixed $invalidation_key
     */
    public function register(string $item, callable $generator, ?callable $invalidation_key = null): void
    {
        $this->registered_items[$item] = [
            'generator' => $generator,
            'invalidation_key' => $invalidation_key
        ];
    }

    public function get(string $item, string $instance): string
    {
        $this->ensureRegistered($item);

        $cache_key = $this->buildKey($item, $instance);
        $cache_item = $this->adapter->getItem($cache_key);
        $generator = $this->registered_items[$item]['generator'];
        $invalidation_key = $this->registered_items[$item]['invalidation_key'];

        if ($this->hot_reload && $invalidation_key !== null && $cache_item->isHit()) {
            $cached_data = $cache_item->get();
            $current_key = $invalidation_key($instance);
            $cached_key = is_array($cached_data) ? ($cached_data['_key'] ?? null) : null;

            if ($current_key !== $cached_key) {
                $cache_item->set(null);
            }
        }

        if (!$cache_item->isHit()) {
            $content = $generator($instance);

            if ($this->hot_reload && $invalidation_key !== null) {
                $cache_item->set([
                    '_key' => $invalidation_key($instance),
                    'content' => $content
                ]);
            } else {
                $cache_item->set(['content' => $content]);
            }

            $this->adapter->save($cache_item);
            $this->trackInstance($item, $instance);

            return $content;
        }

        $this->trackInstance($item, $instance);
        $cached_data = $cache_item->get();

        return is_array($cached_data) ? ($cached_data['content'] ?? '') : '';
    }

    /**
     * @return array<string, string>
     */
    public function getAll(string $item): array
    {
        $this->ensureRegistered($item);

        $instances = $this->getTrackedInstances($item);
        $results = [];

        foreach ($instances as $instance) {
            $results[$instance] = $this->get($item, $instance);
        }

        return $results;
    }

    public function rebuild(string $item, string $instance): string
    {
        $this->ensureRegistered($item);

        $this->adapter->deleteItem($this->buildKey($item, $instance));

        return $this->get($item, $instance);
    }

    /**
     * @return array<string, string>
     */
    public function rebuildAll(string $item): array
    {
        $this->ensureRegistered($item);

        $instances = $this->getTrackedInstances($item);
        $results = [];

        foreach ($instances as $instance) {
            $results[$instance] = $this->rebuild($item, $instance);
        }

        return $results;
    }

    public function delete(string $item, string $instance): bool
    {
        $this->untrackInstance($item, $instance);
        return $this->adapter->deleteItem($this->buildKey($item, $instance));
    }

    public function deleteAll(string $item): bool
    {
        $instances = $this->getTrackedInstances($item);
        $success = true;

        foreach ($instances as $instance) {
            $success = $this->delete($item, $instance) && $success;
        }

        unset($this->instance_tracker[$item]);

        return $success;
    }

    private function ensureRegistered(string $item): void
    {
        if (!isset($this->registered_items[$item])) {
            throw new \InvalidArgumentException("Item '$item' is not registered. Call register() first.");
        }
    }

    private function trackInstance(string $item, string $instance): void
    {
        if (!isset($this->instance_tracker[$item])) {
            $this->instance_tracker[$item] = [];
        }

        if (!in_array($instance, $this->instance_tracker[$item], true)) {
            $this->instance_tracker[$item][] = $instance;
        }
    }

    private function untrackInstance(string $item, string $instance): void
    {
        if (!isset($this->instance_tracker[$item])) {
            return;
        }

        $key = array_search($instance, $this->instance_tracker[$item], true);
        if ($key !== false) {
            unset($this->instance_tracker[$item][$key]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function getTrackedInstances(string $item): array
    {
        return $this->instance_tracker[$item] ?? [];
    }

    private function buildKey(string $item, string $instance): string
    {
        return $this->normalizePart($item) . '.' . $this->normalizePart($instance);
    }

    private function normalizePart(string $part): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $part) ?? $part;
    }

}
