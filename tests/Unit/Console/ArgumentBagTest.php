<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use PHPUnit\Framework\TestCase;
use Tempest\Console\ConsoleArgumentBag;

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
        ];

        $bag = new ConsoleArgumentBag($argv);

        $this->assertCount(3, $bag->all());

        $firstArg = $bag->all()[0];
        $this->assertSame('value', $firstArg->value);
        $this->assertSame(0, $firstArg->position);
        $this->assertNull($firstArg->name);

        $forceFlag = $bag->all()[1];
        $this->assertSame(true, $forceFlag->value);
        $this->assertSame(1, $forceFlag->position);
        $this->assertSame('force', $forceFlag->name);

        $timesFlag = $bag->all()[2];
        $this->assertSame('2', $timesFlag->value);
        $this->assertSame(2, $timesFlag->position);
        $this->assertSame('times', $timesFlag->name);

        $this->assertSame(
            'hello:world',
            $bag->getCommandName(),
        );
    }
}
