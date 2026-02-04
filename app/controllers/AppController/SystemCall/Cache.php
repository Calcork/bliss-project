<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\Controller\SystemcallControllerReport;

class Cache extends SystemCall
{

    /**
     * @param string $section
     * @param string $method
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @param bool[]|float[]|int[]|null[]|string[] $attributes
     */
    public function purgeAll(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        $this->app->purgeCache();
        echo 'Cache purged';
        return SystemcallControllerReport::success();
    }

    /**
     * @param string $section
     * @param string $method
     * @param bool[]|float[]|int[]|null[]|string[] $arguments
     * @param bool[]|float[]|int[]|null[]|string[] $attributes
     */
    public function rebuildAll(string $section, string $method, array $arguments, array $attributes): SystemcallControllerReport
    {
        $this->app->rebuildCache();
        echo 'Cache rebuilt.';
        return SystemcallControllerReport::success();
    }
}