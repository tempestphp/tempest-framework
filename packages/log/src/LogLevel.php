<?php

declare(strict_types=1);

namespace Tempest\Log;

use Monolog\Level;

enum LogLevel: string
{
    /**
     * System is unusable.
     */
    case EMERGENCY = 'emergency';

    /**
     * Errors that require action immediately.
     */
    case ALERT = 'alert';

    /**
     * Important, unexpected errors that require attention.
     */
    case CRITICAL = 'critical';

    /**
     * Runtime errors that do not require immediate action but should be monitored.
     */
    case ERROR = 'error';

    /**
     * Exceptional occurrences that are not errors.
     */
    case WARNING = 'warning';

    /**
     * Uncommon events.
     */
    case NOTICE = 'notice';

    /**
     * Miscellaneous events.
     */
    case INFO = 'info';

    /**
     * Detailed debug information.
     */
    case DEBUG = 'debug';

    public static function fromMonolog(Level $level): self
    {
        return match ($level) {
            Level::Emergency => self::EMERGENCY,
            Level::Alert => self::ALERT,
            Level::Critical => self::CRITICAL,
            Level::Error => self::ERROR,
            Level::Warning => self::WARNING,
            Level::Notice => self::NOTICE,
            Level::Info => self::INFO,
            Level::Debug => self::DEBUG,
        };
    }

    public function toMonolog(): Level
    {
        return match ($this) {
            self::EMERGENCY => Level::Emergency,
            self::ALERT => Level::Alert,
            self::CRITICAL => Level::Critical,
            self::ERROR => Level::Error,
            self::WARNING => Level::Warning,
            self::NOTICE => Level::Notice,
            self::INFO => Level::Info,
            self::DEBUG => Level::Debug,
        };
    }

    /**
     * Determines if this log level is higher than or equal to the given level.
     */
    public function includes(self $level): bool
    {
        return $this->toMonolog()->includes($level->toMonolog());
    }
}
