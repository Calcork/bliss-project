<?php

namespace App\Controllers\AppController\SystemCall;

class Cache extends SystemCall
{
    function purgeAll() : void
    {

        $caches = $this->app->getCacheServiceableServices();

        foreach($caches as $name => $cache) {
            $instances = $cache->getAll('translations') ?: $cache->getAll('route-regex');
            foreach (array_keys($instances) as $instance) {
                $item = $name === 'translator' ? 'translations' : 'route-regex';
                $cache->delete($item, $instance);
            }
            echo "Purged {$name} cache.\n";
        }

        echo 'Purged all services cache.';

    }

    function rebuildAll() : void
    {

        $caches = $this->app->getCacheServiceableServices();

        foreach($caches as $name => $cache) {
            $item = $name === 'translator' ? 'translations' : 'route-regex';
            $cache->rebuildAll($item);
            echo "Rebuilt {$name} cache.\n";
        }

        echo 'Rebuilt all services cache.';

    }

}