<?php

namespace Hizech\Bliss\Route;


enum HttpMethod: int
{
    case GET  = 0;
    case POST = 1;

    public static function fromName(string $name): ?self {

        return match (strtoupper($name)) {
            'GET'  => self::GET,
            'POST' => self::POST,
            default => null
        };

    }
    
}