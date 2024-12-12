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
            ->assertSee('cache:clear')
            ->assertSee('discovery:clear')
            ->assertSee('static:clean')
            ->assertSee('session:clean');

        $this->console
            ->call('clean')
            ->assertSee('static:clean')
            ->assertSee('session:clean')
            ->assertSee('cache:clear')
            ->assertSee('discovery:clear');
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
