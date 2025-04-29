<?php

declare(strict_types=1);

namespace Tempest\Console\Enums;

use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\Console\ConsoleMiddleware;
use Tempest\EventBus\EventBusMiddleware;
use Tempest\Router\HttpMiddleware;

/**
 * Represents available middleware types in Tempest.
 */
enum MiddlewareType: string
{
    case CONSOLE = 'console';
    case HTTP = 'http';
    case EVENT_BUS = 'event-bus';
    case COMMAND_BUS = 'command-bus';

    /**
     * Get the related interface for the middleware type.
     *
     * @return class-string
     */
    public function relatedInterface(): string
    {
        return match ($this) {
            self::CONSOLE => ConsoleMiddleware::class,
            self::HTTP => HttpMiddleware::class,
            self::EVENT_BUS => EventBusMiddleware::class,
            self::COMMAND_BUS => CommandBusMiddleware::class,
        };
    }
}
