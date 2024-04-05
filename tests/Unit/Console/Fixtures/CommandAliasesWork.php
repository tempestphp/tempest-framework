<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use ReflectionMethod;
use PHPUnit\Framework\TestCase;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgumentBag;
use Tests\Tempest\Unit\Console\Fixtures\ListFrameworks;
use Tempest\Console\Exceptions\UnresolvedArgumentsException;

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
