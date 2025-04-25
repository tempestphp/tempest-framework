<?php

declare(strict_types=1);

namespace Tempest\Log\Tests;

use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Log\LogLevel;

/**
 * @internal
 */
#[CoversClass(LogLevel::class)]
final class LogLevelTest extends TestCase
{
    #[DataProvider('levelsProvider')]
    public function test_from_monolog(Level $level, LogLevel $expected): void
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
