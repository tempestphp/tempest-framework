<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Console\ConsoleCommand;

/**
 * @internal
 * @small
 */
final class CommandAliasesWork extends TestCase
{
    public function test_aliases_work()
    {
        $handler = new ReflectionMethod(new ListFrameworks(), 'handle');

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
            'frameworks:list [sortByBest=false] - List all available frameworks.',
            $string
        );
    }
}
