<?php

namespace Hizech\Bliss\Cache\StaticResourceCache;

use Hizech\Bliss\App\Services\StaticResourceCacheInterface;
use Hizech\Bliss\Cache\StorageAdapter\StorageAdapter;
use Hizech\Bliss\Cache\TrackerAdapter\TrackerAdapter;

/**
 * @phpstan-type InstanceGetter callable(?string): (string|list<string>|null)
 * @phpstan-type MetaDataGetter callable(string): string
 * @phpstan-type InstanceValidator callable(string, string, string): bool
 * @phpstan-type ResourceDefinition array{
 *     instance_getter: InstanceGetter,
 *     meta_data_getter: MetaDataGetter,
 *     instance_validator: InstanceValidator
 * }
 *
 *  For file based caching and tracking - item 'translations' instance 'en' produces a folder like $this->cache_dir . '/translations', and a cache file like $this->cache_dir . '/translations/en.cache' and a meta data file like $this->cache_dir . '/translations/en.meta_data'
 */
class StaticResourceCache implements StaticResourceCacheInterface
{

    /**
     * @var array<string, ResourceDefinition>
     */
    protected array $resources = [];

    function __construct(

        private bool           $hot_reload,
        private StorageAdapter $storage_adapter,
        private TrackerAdapter $tracker_adapter

    ) {}

    function isHotReload(): bool
    {
        return $this->hot_reload;
    }

    function getStorageAdapter(): StorageAdapter
    {
        return $this->storage_adapter;
    }

    function getTrackerAdapter(): TrackerAdapter
    {
        return $this->tracker_adapter;
    }

    /**
     * @phpstan-return array<string, ResourceDefinition>
     */
    public function getResources(): array {
        return $this->resources;
    }

    /**
     * @param InstanceGetter $instance_getter
     * @param MetaDataGetter $meta_data_getter
     * @param InstanceValidator $instance_validator
     */
    public function registerResource(
        string $item,
        callable $instance_getter,
        callable $meta_data_getter,
        callable $instance_validator
    ): void {
        $this->resources[$item] = [
            'instance_getter' => $instance_getter,
            'meta_data_getter' => $meta_data_getter,
            'instance_validator' => $instance_validator,
        ];
    }

    /**
     * @return string|array<string, string>
     */
    public function getCacheSmart(string $item, ?string $instance = null): string|array
    {
        if ($instance !== null) {
            return $this->getCacheForInstance($item, $instance);
        }

        $instances = $this->getAllInstances($item);
        $result = [];

        foreach ($instances as $inst) {
            $result[$inst] = $this->getCacheForInstance($item, $inst);
        }

        return $result;
    }

    public function deleteCacheSmart(string $item, ?string $instance = null): void
    {
        if ($instance !== null) {
            $this->getStorageAdapter()->deleteCache($item, $instance);
            $this->getTrackerAdapter()->deleteMetaData($item, $instance);
            return;
        }

        $instances = $this->getAllInstances($item);

        foreach ($instances as $inst) {
            $this->getStorageAdapter()->deleteCache($item, $inst);
            $this->getTrackerAdapter()->deleteMetaData($item, $inst);
        }
    }

    public function rebuildCacheSmart(string $item, ?string $instance = null): void
    {
        if ($instance !== null) {
            $this->rebuildIfNeeded($item, $instance);
            return;
        }

        $instances = $this->getAllInstances($item);

        foreach ($instances as $inst) {
            $this->rebuildIfNeeded($item, $inst);
        }
    }

    private function getCacheForInstance(string $item, string $instance): string
    {
        if (!isset($this->resources[$item])) {
            throw new \InvalidArgumentException("Resource '$item' not registered");
        }

        $cached = $this->getStorageAdapter()->getCache($item, $instance);

        if ($cached !== null) {
            if ($this->isHotReload() && !$this->isFresh($item, $instance)) {
                return $this->rebuild($item, $instance);
            }
            return $cached;
        }

        return $this->rebuild($item, $instance);
    }

    private function isFresh(string $item, string $instance): bool
    {
        $stored_meta = $this->getTrackerAdapter()->getMetaData($item, $instance);

        if ($stored_meta === null) {
            return false;
        }

        $validator = $this->resources[$item]['instance_validator'];
        return $validator($item, $instance, $stored_meta);
    }

    private function rebuildIfNeeded(string $item, string $instance): void
    {
        if ($this->isHotReload() && !$this->isFresh($item, $instance)) {
            $this->rebuild($item, $instance);
        } elseif ($this->getStorageAdapter()->getCache($item, $instance) === null) {
            $this->rebuild($item, $instance);
        }
    }

    private function rebuild(string $item, string $instance): string
    {
        if (!isset($this->resources[$item])) {
            throw new \InvalidArgumentException("Resource '$item' not registered");
        }

        $getter = $this->resources[$item]['instance_getter'];
        $content = $getter($instance);

        if (is_array($content)) {
            $content = '';
        }

        $meta_getter = $this->resources[$item]['meta_data_getter'];
        $meta = $meta_getter($instance);

        $this->getStorageAdapter()->saveCache($item, $instance, $content);
        $this->getTrackerAdapter()->saveMetaData($item, $instance, $meta);

        return $content;
    }

    /**
     * @return list<string>
     */
    private function getAllInstances(string $item): array
    {
        if (!isset($this->resources[$item])) {
            return [];
        }

        $getter = $this->resources[$item]['instance_getter'];
        $result = $getter(null);

        if (is_string($result)) {
            return [$result];
        }

        return $result;
    }
}
