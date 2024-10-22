<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ResolveOrRescueMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function it_can_find_a_single_similar_command(): void
    {
        $this->console
            ->call('discovery:sta')
            ->assertSee('Did you mean discovery:status?');

        $this->console
            ->call('bascovery:status')
            ->assertSee('Did you mean discovery:status?');
    }

    #[Test]
    public function it_does_not_duplicate_completed_commands(): void
    {
        $formatOutput = static fn (string $buffer) => str($buffer)
            ->trim()
            ->remove(['[',']'])
            ->explode('/')
            ->all();

        $output = $this->console
            ->call('discovery')
            ->getBuffer(fn (array $buffer) => $formatOutput(array_pop($buffer)));

        $this->assertContains('discovery:status', $output);
        $this->assertContains('discovery:clear', $output);

        $this->assertCount(2, $output);
    }
}
