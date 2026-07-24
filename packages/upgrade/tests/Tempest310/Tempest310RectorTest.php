<?php

namespace Tempest\Upgrade\Tests\Tempest310;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest310RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest310_rector.php');
    }

    #[Test]
    public function priority_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/PriorityNamespaceChange.input.php')
            ->assertContains('use Tempest\Support\Priority;')
            ->assertNotContains('use Tempest\Core\Priority;');
    }

    #[Test]
    public function fully_qualified_priority(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedPriority.input.php')
            ->assertContains('Tempest\Support\Priority')
            ->assertNotContains('Tempest\Core\Priority');
    }
}
