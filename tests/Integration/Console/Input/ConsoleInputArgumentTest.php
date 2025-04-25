<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Input;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Tempest\Console\Input\ConsoleInputArgument;

/**
 * @internal
 */
#[CoversNothing]
final class ConsoleInputArgumentTest extends TestCase
{
    public function test_parse_named_arguments(): void
    {
        $input = ConsoleInputArgument::fromString('--flag=abc');
        $this->assertSame('abc', $input->value);

        $input = ConsoleInputArgument::fromString('--flag');
        $this->assertTrue($input->value);

        $input = ConsoleInputArgument::fromString('--flag=true');
        $this->assertTrue($input->value);

        $input = ConsoleInputArgument::fromString('--flag=false');
        $this->assertFalse($input->value);

        $input = ConsoleInputArgument::fromString('--flag=');
        $this->assertNull($input->value);

        $input = ConsoleInputArgument::fromString('--flag="abc"');
        $this->assertSame('abc', $input->value);

        $input = ConsoleInputArgument::fromString('--foo-bar="baz"');
        $this->assertSame('foo-bar', $input->name);
        $this->assertSame('baz', $input->value);

        $input = ConsoleInputArgument::fromString('--fooBar');
        $this->assertSame('foo-bar', $input->name);

        $input = ConsoleInputArgument::fromString('--noFooBar');
        $this->assertSame('foo-bar', $input->name);
        $this->assertSame(false, $input->value);

        $input = ConsoleInputArgument::fromString('--no-interaction');
        $this->assertSame('interaction', $input->name);
        $this->assertSame(false, $input->value);

        $input = ConsoleInputArgument::fromString('--no-interaction=true');
        $this->assertSame('interaction', $input->name);
        $this->assertSame(false, $input->value);

        $input = ConsoleInputArgument::fromString('--no-interaction=false');
        $this->assertSame('interaction', $input->name);
        $this->assertSame(true, $input->value);

        $input = ConsoleInputArgument::fromString('--no-foo=baz');
        $this->assertSame('no-foo', $input->name);
        $this->assertSame('baz', $input->value);
    }
}
