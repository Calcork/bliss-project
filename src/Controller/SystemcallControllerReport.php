<?php

namespace Hizech\Bliss\Controller;

readonly class SystemcallControllerReport
{

    function __construct(
        public SystemcallControllerReportCode $status,
        public ?string                        $message = null,
    )
    {}

    static function Success() : self {
        return new self(SystemcallControllerReportCode::Success);
    }

    static function thrown(\Throwable $throwable) : self
    {
        return new self(SystemcallControllerReportCode::Thrown, $throwable->getMessage());
    }

    static function ranButFailed(?string $message = null) : self
    {
        return new self(SystemcallControllerReportCode::RanButFailed, $message);
    }

}