<?php

declare(strict_types=1);

namespace Tempest\Log\Tests;

use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Log\LogLevel;

/**
 * @internal
 */
final class LogLevelTest extends TestCase
{
    #[Test]
    #[DataProvider('levelsProvider')]
    public function from_monolog(Level $level, LogLevel $expected): void
    {
        $this->assertSame($expected, LogLevel::fromMonolog($level));
    }

    public static function levelsProvider(): array
    {
        return [
            [Level::Emergency, LogLevel::EMERGENCY],
            [Level::Alert,     LogLevel::ALERT],
            [Level::Critical,  LogLevel::CRITICAL],
            [Level::Error,     LogLevel::ERROR],
            [Level::Warning,   LogLevel::WARNING],
            [Level::Notice,    LogLevel::NOTICE],
            [Level::Info,      LogLevel::INFO],
            [Level::Debug,     LogLevel::DEBUG],
        ];
    }
}
