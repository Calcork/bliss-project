<?php

namespace Hizech\Bliss\Logger;

use DateTime;
use DateTimeZone;
use Hizech\Bliss\App\Services\Logger;
use Hizech\Bliss\Misc\Util;
use Throwable;

class SimpleLogger implements Logger
{
    private string $logs_folder;
    private DateTimeZone $timezone;

    function __construct(string $logs_folder, DateTimeZone $timezone)
    {
        $this->logs_folder = rtrim($logs_folder, '/');
        $this->timezone = $timezone;
    }

    public function log(string $channel, Throwable $content) : void {

        $time = new DateTime('now', $this->timezone);
        $ymd = $time->format('Y-m-d');
        $full_time = $time->format('c');
        $file_name = $ymd . '.log';
        $folder_path = Util::joinPath($this->logs_folder, $channel);
        $full_path = Util::joinPath($folder_path, $file_name);

        $message = self::formatMessage($channel, $full_time, $content);

        if(!is_dir($folder_path)) mkdir($folder_path, 0755, true);
        file_put_contents($full_path, $message, FILE_APPEND);

    }

    private static function formatMessage(
        string $channel,
        string $time_str,
        Throwable $content
    ): string {
        $divider = str_repeat('-', 80);

        $content_str = sprintf(
            "%s: %s in %s(%formHtmlResult)\n\nStack trace:\n%s",
            get_class($content),
            $content->getMessage(),
            $content->getFile(),
            $content->getLine(),
            $content->getTraceAsString()
        );

        return sprintf(
            "{channel:`%s`} [%s]\n\n%s\n%s%s",
            $channel,
            $time_str,
            trim($content_str),
            $divider,
            PHP_EOL
        );
    }

}
