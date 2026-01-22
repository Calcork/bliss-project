<?php

namespace Hizech\Bliss\Cache\StorageAdapter;

interface StorageAdapter
{

    /**
     * @param string $item
     * @param string $instance
     * @return string|null String on success, null on failure to obtain
     */
    public function getCache(string $item, string $instance) : null|string;

    /**
     * Used to update not just create
     * @param string $item
     * @param string $instance
     * @param string $content
     * @return bool True on success, false on failure
     */
    public function saveCache(string $item, string $instance, string $content) : bool;

    /**
     * Deletes content but also record itself
     * @param string $item
     * @param string $instance
     * @return bool True on success, false on failure
     */
    public function deleteCache(string $item, string $instance) : bool;

}