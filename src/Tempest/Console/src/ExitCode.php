<?php

declare(strict_types=1);

namespace Tempest\Console;

/**
 * You're free to return any int between 0 and 255 from a console command as exit code.
 * The meaning of these integer values will be determined by your application.
 * Alternatively, Tempest provides a closed set of predefined exit codes via this enum.
 * The exit codes listed here are used by Tempest and assigned a fixed meaning.
 */
enum ExitCode: int
{
    case SUCCESS = 0;
    case ERROR = 1;
    case INVALID = 2;
    case CANCELLED = 25;
    case CANNOT_EXECUTE = 126;
    case COMMAND_NOT_FOUND = 127;
    case INVALID_EXIT_CODE = 128;
    case TERMINATED = 130;
}
