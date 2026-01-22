<?php

namespace Hizech\Bliss\Cache\TrackerAdapter;

interface TrackerAdapter
{
    /**
     * Used to update not just create
     * @return bool True on success, false on failure
     */
    function saveMetaData(string $item, string $instance, string $content): bool;
    function getMetaData(string $item, string $instance): ?string;

    /**
     * Deletes content but also deletes record itself
     * @param string $item
     * @param string $instance
     * @return bool True on success, false on failure
     */
    function deleteMetaData(string $item, string $instance) : bool;
}