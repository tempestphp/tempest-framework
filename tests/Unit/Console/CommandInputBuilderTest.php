<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use PHPUnit\Framework\TestCase;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleArgumentDefinition;
use Tempest\Console\ConsoleCommandDefinition;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\Exceptions\UnresolvedArgumentsException;

/**
 * @internal
 * @small
 */
class CommandInputBuilderTest extends TestCase
{
    public function test_resolving_arguments_works()
    {
        $builder = new ConsoleInputBuilder(
            new ConsoleCommandDefinition([
                new ConsoleArgumentDefinition('name', 'string', null, hasDefault: false, position: 0),
                new ConsoleArgumentDefinition('optional', 'bool', true, hasDefault: true, position: 1),
            ]),
            new ConsoleArgumentBag(['tempest', 'some:command', 'value'])
        );

        $output = $builder->build();

        $this->assertSame(
            ['value', true],
            $output
        );
    }

    public function test_flags_are_casted_to_bool()
    {
        $builder = new ConsoleInputBuilder(
            new ConsoleCommandDefinition([
                new ConsoleArgumentDefinition('force', 'bool', false, hasDefault: false, position: 0),
            ]),
            new ConsoleArgumentBag(['tempest', 'some:command', '--force'])
        );

        $output = $builder->build();

        $this->assertSame(
            [true],
            $output
        );
    }

    public function test_argument_cannot_be_resolved_twice()
    {
        $builder = new ConsoleInputBuilder(
            new ConsoleCommandDefinition([
                new ConsoleArgumentDefinition('name', 'string', null, hasDefault: false, position: 0),
            ]),
            new ConsoleArgumentBag(['tempest', 'some:command', 'value', '--name=other'])
        );

        try {
            $builder->build();

            $this->fail('Expected exception to be thrown');
        } catch (UnresolvedArgumentsException $e) {
            $this->assertCount(1, $e->getArguments());
            $this->assertSame('name', $e->getArguments()[0]->name);
        }
    }

    public function test_both_named_and_positional_arguments_can_be_used()
    {
        $builder = new ConsoleInputBuilder(
            new ConsoleCommandDefinition([
                new ConsoleArgumentDefinition('name', 'string', null, hasDefault: false, position: 0),
                new ConsoleArgumentDefinition('test', 'string', null, hasDefault: false, position: 1),
                new ConsoleArgumentDefinition('foo', 'string', null, hasDefault: false, position: 2),
            ]),
            new ConsoleArgumentBag(['tempest', 'some:command', 'value', '--test=other', 'bar'])
        );

        $output = $builder->build();

        $this->assertSame(
            ['value', 'other', 'bar'],
            $output
        );
    }
}
