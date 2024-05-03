<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
final class CommandAliasesWork extends TestCase
{
    public function test_aliases_work()
    {
        $this
            ->console
            ->call('f:l')
            ->assertContains('list');
    }
}
