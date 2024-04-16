<?php

declare(strict_types=1);

namespace Tests\Tempest\Console;

/**
 * @internal
 * @small
 */
class InteractiveCommandTest extends TestCase
{
    public function test_interactive_command(): void
    {
        $this
            ->console
            ->call('interactive')
            ->write('abc')
            ->left()
            ->enter()
            ->assertContains('abc');
    }
}
