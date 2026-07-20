<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CommandAliasesWork extends FrameworkIntegrationTestCase
{
    #[Test]
    public function aliases_work(): void
    {
        $this->console
            ->call('f:l')
            ->assertContains('list');
    }
}
