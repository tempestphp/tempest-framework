<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use function Tempest\attribute;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\RenderConsoleCommand;
use Tests\Tempest\Unit\Console\Fixtures\MyConsole;

class RenderConsoleCommandTest extends TestCase
{
    /** @test */
    public function test_render()
    {
        $handler = new ReflectionMethod(new MyConsole(), 'handle');

        $consoleCommand = attribute(ConsoleCommand::class)->in($handler)->first();

        $consoleCommand->setHandler($handler);

        $string = str_replace(
            [
                ConsoleStyle::FG_BLUE->value,
                ConsoleStyle::FG_DARK_BLUE->value,
                ConsoleStyle::RESET->value,
                ConsoleStyle::ESC->value,
            ],
            '',
            (new RenderConsoleCommand())($consoleCommand)
        );

        $this->assertSame(
            'test <path> [times=1] [--force=false] - description',
            $string
        );
    }
}
