<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use ReflectionMethod;
use PHPUnit\Framework\TestCase;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgumentBag;
use Tests\Tempest\Unit\Console\Fixtures\ListFrameworks;
use Tempest\Console\Exceptions\UnresolvedArgumentsException;

final class ArgumentBagTest extends TestCase
{

    public function test_argument_bag_works(): void
    {
        $argv = [
            'tempest',
            'hello:world',
            'value',
            '--force',
            '--times=2',
            '--option',
            'option-value',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $this->assertCount(5, $bag->all());

        $this->assertSame('value', $bag->get(0)->getValue());
        $this->assertSame(true, $bag->get('force')->getValue());

        $this->assertSame("2", $bag->get('times')->getValue());

        $bag->set('times', 3);

        $this->assertSame(3, $bag->get('times')->getValue());

        $this->assertCount(5, $bag->all());

        $this->assertTrue(
            $bag->has('force')
        );

        $this->assertFalse(
            $bag->has('unknown')
        );

        $this->assertSame(
            'hello:world',
            $bag->getCommandName(),
        );
    }

    public function test_resolves_command_input(): void
    {
        $bag = new ConsoleArgumentBag([
            'tempest',
            'frameworks:list',
            '--sortByBest',
        ]);

        $this->assertTrue(
            $bag->get('sortByBest')->getValue()
        );

        $handler = new ReflectionMethod(new ListFrameworks(), 'handle');

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

        $consoleCommand->setHandler($handler);

        $input = $bag->resolveInput($consoleCommand);

        $this->assertCount(1, $input->arguments);
        $this->assertTrue($input->arguments[0]->getValue());
        $this->assertSame('sortByBest', $input->arguments[0]->name);
    }

    public function test_resolving_can_throw(): void
    {
        $bag = new ConsoleArgumentBag([
            'tempest',
            'frameworks:list',
            '--sortByBest',
            'undefinedArg'
        ]);

        $this->assertTrue(
            $bag->get('sortByBest')->getValue()
        );

        $handler = new ReflectionMethod(new ListFrameworks(), 'handle');

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

        $consoleCommand->setHandler($handler);

        try {
            $input = $bag->resolveInput($consoleCommand);

            $this->assertCount(1, $input->arguments);
            $this->assertTrue($input->arguments[0]->getValue());
            $this->assertSame('sortByBest', $input->arguments[0]->name);
        }  catch (UnresolvedArgumentsException $e) {
            $this->assertSame('undefinedArg', $e->getArguments()[0]->value);
        }
    }

}
