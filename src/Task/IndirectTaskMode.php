<?php

namespace Hizech\Bliss\Task;

/**
 * Mode for processing indirect tasks (queues and scheduled tasks).
 *
 * - Cron: Tasks processed via web endpoint, triggered by external cron service
 * - Worker: Tasks processed by background worker processes (requires SSH)
 */
enum IndirectTaskMode: string
{
    case Cron = 'cron';
    case Worker = 'worker';

    /**
     * Create from string value with validation.
     */
    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'cron' => self::Cron,
            'worker' => self::Worker,
            default => throw new \InvalidArgumentException(
                "Invalid INDIRECT_TASK_MODE: '{$value}'. Must be 'cron' or 'worker'."
            ),
        };
    }
}
