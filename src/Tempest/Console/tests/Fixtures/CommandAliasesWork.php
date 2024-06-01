<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tests\Tempest\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class CommandAliasesWork extends ConsoleIntegrationTestCase
{
    public function test_aliases_work()
    {
        $this
            ->console
            ->call('f:l')
            ->assertContains('list');
    }
}
