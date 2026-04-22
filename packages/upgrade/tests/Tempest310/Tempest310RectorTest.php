<?php

namespace Tempest\Upgrade\Tests\Tempest310;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

#[RunTestsInSeparateProcesses]
final class Tempest310RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest310_rector.php');
    }

    public function test_priority_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/PriorityNamespaceChange.input.php')
            ->assertContains('use Tempest\Support\Priority;')
            ->assertNotContains('use Tempest\Core\Priority;');
    }

    public function test_fully_qualified_priority(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedPriority.input.php')
            ->assertContains('Tempest\Support\Priority')
            ->assertNotContains('Tempest\Core\Priority');
    }
}
