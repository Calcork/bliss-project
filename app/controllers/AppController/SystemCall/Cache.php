<?php

namespace App\Controllers\AppController\SystemCall;

use Hizech\Bliss\Controller\SystemcallControllerReport;

class Cache extends SystemCall
{
    public function purgeAll(): SystemcallControllerReport
    {
        $this->app->purgeCache();
        return SystemcallControllerReport::Success();
    }

    public function rebuildAll(): SystemcallControllerReport
    {
        $this->app->rebuildCache();
        return SystemcallControllerReport::Success();
    }
}