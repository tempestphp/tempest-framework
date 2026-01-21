<?php

namespace Integration\App;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class TempestSubsplitCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function test_subsplit(): void
    {
        $this->console
            ->call('tempest:subsplit')
            ->assertSuccess();
    }
}