<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CommandAliasesWork extends FrameworkIntegrationTestCase
{
    public function test_aliases_work(): void
    {
        $this->console
            ->call('f:l')
            ->assertContains('list');
    }
}
