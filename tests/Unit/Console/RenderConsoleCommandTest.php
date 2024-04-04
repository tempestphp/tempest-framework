<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tests\Tempest\Unit\Console\Fixtures\MyConsole;

/**
 * @internal
 * @small
 */
class RenderConsoleCommandTest extends TestCase
{
    public function test_render()
    {
        $handler = new ReflectionMethod(new MyConsole(), 'handle');

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

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
