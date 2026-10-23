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
    public function container_implementations_receive_the_optional_lifetime(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ContainerImplementation.input.php')
            ->assertContains('use Tempest\\Container\\Lifetime;')
            ->assertContains('public function singleton(string $className, mixed $definition, string|UnitEnum|null $tag = null, ?Lifetime $lifetime = null): self');
    }

    #[Test]
    public function unrelated_or_already_updated_methods_are_unchanged(): void
    {
        $this->assertSame('', $this->rector->runFixture(__DIR__ . '/Fixtures/Unchanged.input.php')->actual);
    }
}
