<?php

namespace App\Lib\RateLimiter;

use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

class RateLimiter
{
    private RateLimiterFactory $factory;

    function __construct(int $limit, string $interval)
    {
        $storage = new CacheStorage(new ApcuAdapter('rate_limiter'));

        $this->factory = new RateLimiterFactory(
            [
                'id' => 'http_request',
                'policy' => 'sliding_window',
                'limit' => $limit,
                'interval' => $interval,
            ],
            $storage
        );
    }

    function isLimited(Request $request): bool
    {
        $key = $request->getClientIp() ?? 'unknown';
        $limiter = $this->factory->create($key);
        $limit = $limiter->consume();

        return !$limit->isAccepted();
    }
}
