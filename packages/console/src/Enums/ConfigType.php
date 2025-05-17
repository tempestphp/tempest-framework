<?php

declare(strict_types=1);

namespace Tempest\Console\Enums;

/**
 * Represents available config types in Tempest.
 */
enum ConfigType: string
{
    case DATABASE = 'database';
    case TWIG = 'twig';
    case BLADE = 'blade';
    case VIEW = 'view';
    case EVENT_BUS = 'event-bus';
    case COMMAND_BUS = 'command-bus';
    case LOG = 'log';
    case CONSOLE = 'console';
}
