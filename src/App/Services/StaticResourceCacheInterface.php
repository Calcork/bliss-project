<?php

namespace Hizech\Bliss\App\Services;

/**
 * @phpstan-import-type InstanceGetter from \Hizech\Bliss\Cache\StaticResourceCache\StaticResourceCache
 * @phpstan-import-type MetaDataGetter from StaticResourceCacheInterface
 * @phpstan-import-type InstanceValidator from StaticResourceCacheInterface
 * @phpstan-import-type ResourceDefinition from StaticResourceCacheInterface
 */
interface StaticResourceCacheInterface
{
    public function registerResource(
        string $item,
        /**
         * @phpstan-param InstanceGetter $instance_getter
         */
        callable $instance_getter,
        /**
         * @phpstan-param MetaDataGetter $meta_data_getter
         */
        callable $meta_data_getter,
        /**
         * @phpstan-param InstanceValidator $instance_validator
         */
        callable $instance_validator
    ): void;

    /**
     * In hot reload, if nonexistent creates cache, if existent but not fresh rebuild cache, serves
     * In NONE hot reload, if nonexistent creates cache, serves
     * @param ?string $instance If null, for all instances
     * @return string|array<string, string>
     */
    public function getCacheSmart(string $item, ?string $instance = null): string|array;

    /**
     * Delets both the records from tracker, and also the content from the cache adapter
     * @param string $item
     * @param string|null $instance If null all instances
     * @return void
     */
    public function deleteCacheSmart(string $item, ?string $instance = null): void;

    /**
     * Checks freshness, if fresh skips, if not fresh, fetches freshest source and saves cache through adapter
     * @param string $item
     * @param string|null $instance If null all instances of item
     * @return void
     */
    public function rebuildCacheSmart(string $item, ?string $instance = null): void;
}