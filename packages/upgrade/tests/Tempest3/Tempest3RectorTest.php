<?php

namespace Tempest\Upgrade\Tests\Tempest3;

use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest3RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest30_rector.php');
    }

    public function test_map_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MapNamespaceChange.input.php')
            ->assertContains('use function Tempest\Mapper\map;')
            ->assertNotContains('use function Tempest\map;');
    }

    public function test_make_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/MakeNamespaceChange.input.php')
            ->assertContains('use function Tempest\Mapper\make;')
            ->assertNotContains('use function Tempest\make;');
    }

    public function test_fully_qualified_map_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMapCall.input.php')
            ->assertContains('use Tempest\Mapper\map;')
            ->assertContains('return map($data)->to(Author::class);');
    }

    public function test_fully_qualified_make_call(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedMakeCall.input.php')
            ->assertContains('use Tempest\Mapper\make;')
            ->assertContains('return make(Author::class)');
    }
}
