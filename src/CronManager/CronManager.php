<?php

namespace Hizech\Bliss\CronManager;

use Hizech\Bliss\CronManager\CronJobFetcher\CronJobFetcher;
use Symfony\Component\Process\Process;

class CronManager
{

    function __construct(
        private CronJobFetcher $cron_job_fetcher,
    ) {}

    private function runJob(CronJob $job) : int {

        $process = new Process($job->command);
        return $process->run();

    }

    /**
     * @return array<string, int> Key is name of job, value is the exit code in int
     */
    function runAllDueJobs() : array {

        $jobs = $this->cron_job_fetcher->getAllCronJobs();
        $jobs_exits = [];

        foreach ($jobs as $name => $job) {

            if($this->cron_job_fetcher->isCronJobDue($name)) {

                $jobs_exits[$name] = $this->runJob($job);
                $this->cron_job_fetcher->markCronJobDueStatus($name, false);

            }

        }

        return $jobs_exits;
    }

}