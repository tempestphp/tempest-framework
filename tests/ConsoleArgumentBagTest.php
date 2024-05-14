<?php

declare(strict_types=1);

namespace Tests\Tempest\Console;

use Tempest\Console\Input\ConsoleArgumentBag;

/**
 * @internal
 * @small
 */
final class ConsoleArgumentBagTest extends TestCase
{
    public function test_argument_bag_works(): void
    {
        $argv = [
            'tempest',
            'hello:world',
            'value',
            '--force',
            '--times=2',
        ];

        $bag = new ConsoleArgumentBag($argv);

        $this->assertCount(3, $bag->all());

        $firstArg = $bag->all()[0];
        $this->assertSame('value', $firstArg->value);
        $this->assertSame(0, $firstArg->position);
        $this->assertNull($firstArg->name);

        $forceFlag = $bag->all()[1];
        $this->assertSame(true, $forceFlag->value);
        $this->assertSame(null, $forceFlag->position);
        $this->assertSame('force', $forceFlag->name);

        $timesFlag = $bag->all()[2];
        $this->assertSame('2', $timesFlag->value);
        $this->assertSame(null, $timesFlag->position);
        $this->assertSame('times', $timesFlag->name);

        $this->assertSame(
            'hello:world',
            $bag->getCommandName(),
        );
    }

    public function test_positional_vs_named_input(): void
    {
        $this->console
            ->call('complex a --c=c --b=b --flag')
            ->assertContains('abc')
            ->assertContains('true')
        ;
    }
}
