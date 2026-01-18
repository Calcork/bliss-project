<?php

namespace Hizech\Bliss\CronManager\CronJobFetcher;

use Hizech\Bliss\CronManager\CronJob;

interface CronJobFetcher
{

    function isCronJobDue(string $cronjob_name) : bool;

    function markCronJobDueStatus(string $cronjob_name, bool $is_due);

    /**
     * @return array<string, CronJob> Returns the array, names as key, cronjob object as values
     */
    function getAllCronJobs() : array;

}