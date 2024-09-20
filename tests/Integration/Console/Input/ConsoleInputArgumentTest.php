<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Input;

use PHPUnit\Framework\TestCase;
use Tempest\Console\Input\ConsoleInputArgument;

/**
 * @internal
 */
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
    }
}
