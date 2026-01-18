<?php

namespace Hizech\Bliss\CronManager\CronJobFetcher;

use Hizech\Bliss\CronManager\CronJob;

class CpanelCronJobFetcher implements CronJobFetcher
{
    
    private ?array $jobs_data = null;

    function __construct(
        private string $cronjobs_file_path,
    ) {}

    function isCronJobDue(string $cronjob_name): bool
    {
        $jobs_data = $this->getJobsData();

        if (!isset($jobs_data[$cronjob_name])) {
            return false;
        }

        $schedule = $jobs_data[$cronjob_name]['schedule'];
        return $this->matchesCronExpression($schedule);
    }

    function markCronJobDueStatus(string $cronjob_name, bool $is_due): void
    {
        // No-op for cPanel - schedule is time-based, not status-based
    }

    /**
     * @return array<string, CronJob>
     */
    function getAllCronJobs(): array
    {
        $jobs_data = $this->getJobsData();

        $jobs = [];
        foreach ($jobs_data as $name => $data) {
            $jobs[$name] = new CronJob(
                description: $data['description'],
                command: $data['command'],
            );
        }

        return $jobs;
    }

    private function getJobsData(): array
    {
        if ($this->jobs_data === null) {
            if (!file_exists($this->cronjobs_file_path)) {
                $this->jobs_data = [];
            } else {
                $this->jobs_data = require $this->cronjobs_file_path;
            }
        }

        return $this->jobs_data;
    }

    private function matchesCronExpression(string $expression): bool
    {
        $parts = preg_split('/\s+/', trim($expression));

        if (count($parts) !== 5) {
            return false;
        }

        [$minute, $hour, $day, $month, $weekday] = $parts;

        $now = new \DateTime();

        return $this->matchesPart($minute, (int) $now->format('i'))
            && $this->matchesPart($hour, (int) $now->format('G'))
            && $this->matchesPart($day, (int) $now->format('j'))
            && $this->matchesPart($month, (int) $now->format('n'))
            && $this->matchesPart($weekday, (int) $now->format('w'));
    }

    private function matchesPart(string $pattern, int $value): bool
    {
        // Wildcard
        if ($pattern === '*') {
            return true;
        }

        // Step values (*/n)
        if (str_starts_with($pattern, '*/')) {
            $step = (int) substr($pattern, 2);
            return $step > 0 && $value % $step === 0;
        }

        // List (n,m,o)
        if (str_contains($pattern, ',')) {
            $values = array_map('intval', explode(',', $pattern));
            return in_array($value, $values);
        }

        // Range (n-m)
        if (str_contains($pattern, '-')) {
            [$min, $max] = array_map('intval', explode('-', $pattern));
            return $value >= $min && $value <= $max;
        }

        // Exact value
        return (int) $pattern === $value;
    }

}
