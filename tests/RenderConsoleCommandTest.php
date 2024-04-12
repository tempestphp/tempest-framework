<?php

declare(strict_types=1);

namespace Tests\Tempest\Console;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Testing\TestConsoleOutput;
use Tests\Tempest\Console\Fixtures\MyConsole;

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

        $output = new TestConsoleOutput();

        (new RenderConsoleCommand($output))($consoleCommand);

        $this->assertSame(
            'test <path> [times=1] [--force=false] - description',
            trim($output->getLinesWithoutFormatting()[0]),
        );
    }
}
