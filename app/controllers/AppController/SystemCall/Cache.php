<?php

namespace App\Controllers\AppController\SystemCall;

class Cache extends SystemCall
{
    function purgeAll(): void
    {
        $caches = $this->app->getCacheServiceableServices();

        foreach ($caches as $name => $cache) {
            $item = $name === 'translator' ? 'translations' : 'route-regex';
            $cache->deleteCacheSmart($item, null);
            echo "Purged {$name} cache.\n";
        }

        echo 'Purged all services cache.';
    }

    function rebuildAll(): void
    {
        $caches = $this->app->getCacheServiceableServices();

        foreach ($caches as $name => $cache) {
            $item = $name === 'translator' ? 'translations' : 'route-regex';
            $cache->rebuildCacheSmart($item, null);
            echo "Rebuilt {$name} cache.\n";
        }

        echo 'Rebuilt all services cache.';
    }
}