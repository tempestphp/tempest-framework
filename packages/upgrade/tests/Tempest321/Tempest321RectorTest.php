<?php

namespace Tempest\Upgrade\Tests\Tempest321;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest321RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest321_rector.php');
    }

    #[Test]
    public function idempotency_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/IdempotencyNamespaceChange.input.php')
            ->assertContains('use Tempest\Idempotency\Idempotent;')
            ->assertContains('use Tempest\Idempotency\IdempotentRoute;')
            ->assertContains('use Tempest\Idempotency\IdempotencyKeyWasAlreadyUsed;')
            ->assertNotContains('Tempest\Idempotency\Attributes')
            ->assertNotContains('Tempest\Idempotency\Exceptions');
    }

    #[Test]
    public function fully_qualified_idempotency_store(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedIdempotencyStore.input.php')
            ->assertContains('IdempotencyStore')
            ->assertContains('IdempotencyRecord')
            ->assertNotContains('Tempest\Idempotency\Store');
    }
}
