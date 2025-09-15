<?php

declare(strict_types=1);

namespace Tempest\Http;

enum Method: string
{
    case GET = 'GET';
    case HEAD = 'HEAD';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case CONNECT = 'CONNECT';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case PATCH = 'PATCH';

    public function isSpoofable(): bool
    {
        return match ($this) {
            Method::PUT, Method::PATCH, Method::DELETE => true,
            default => false,
        };
    }
}
