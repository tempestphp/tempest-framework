<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Support\str;

/**
 * @internal
 */
final class ResolveOrRescueMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function test_similar_commands(): void
    {
        $this->console
            ->call('discovery:sta')
            ->assertSee('Did you mean discovery:status?');

        $this->console
            ->call('bascovery:status')
            ->assertSee('Did you mean discovery:status?');

        $this->console
            ->call('c:cl')
            ->assertSee('Did you mean cache:clear?');

        $this->console
            ->call('generate')
            ->assertSee('discovery:generate')
            ->assertSee('static:generate');

        $this->console
            ->call('gen')
            ->assertSee('discovery:generate')
            ->assertSee('static:generate');

        $this->console
            ->call('clear')
            ->assertSee('[0] cache:clear')
            ->assertSee('[1] discovery:clear')
            ->assertSee('[2] static:clean')
            ->assertSee('[3] session:clean');

        $this->console
            ->call('clean')
            ->assertSee('[0] static:clean')
            ->assertSee('[1] session:clean')
            ->assertSee('[2] cache:clear')
            ->assertSee('[3] discovery:clear');
    }

    #[Test]
    public function it_does_not_duplicate_completed_commands(): void
    {
        $formatOutput = static fn (string $buffer) => str($buffer)
            ->trim()
            ->explode("\n")
            ->map(fn (string $line) => str($line)->afterLast(' ')->trim()->toString())
            ->toArray();

        $output = $this->console
            ->call('discovery')
            ->getBuffer(fn (array $buffer) => $formatOutput(array_pop($buffer)));

        $this->assertContains('discovery:status', $output);
        $this->assertContains('discovery:clear', $output);

        $this->assertNotEmpty($output);
    }
}
