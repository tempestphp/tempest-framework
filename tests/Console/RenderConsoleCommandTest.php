<?php

namespace Tests\Tempest\Console;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\RenderConsoleCommand;
use function Tempest\attribute;

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
            (new RenderConsoleCommand)($consoleCommand)
        );

        $this->assertSame(
            'test <path> [times=1] [--force=false] - description',
            $string
        );
    }
}

class MyConsole
{
    #[ConsoleCommand(
        name: 'test',
        description: 'description',
    )]
    public function handle(
        string $path,
        int $times=1,
        bool $force = false,
    ) {}
}