<?php

namespace Tempest\Upgrade\Tests\Tempest314;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

#[RunTestsInSeparateProcesses]
final class Tempest314RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest314_rector.php');
    }

    public function test_connection_implementation_methods_are_added(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ConnectionImplementation.input.php')
            ->assertContains('public function inTransaction(): bool')
            ->assertContains('public function ping(): bool')
            ->assertContains('public function reconnect(): void')
            ->assertContains('return false;');
    }

    public function test_kernel_implementation_is_updated(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/KernelImplementation.input.php')
            ->assertContains('public function shutdown(int|string $status = \'\'): void')
            ->assertContains('return;')
            ->assertNotContains('return $this;')
            ->assertNotContains('public function shutdown(): self');
    }

    public function test_aliased_interface_implementations_are_updated(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/AliasedImplementations.input.php')
            ->assertContains('public function inTransaction(): bool')
            ->assertContains('public function ping(): bool')
            ->assertContains('public function reconnect(): void');
    }

    public function test_existing_interface_methods_are_not_overwritten(): void
    {
        $this->assertSame(
            '',
            $this->rector
                ->runFixture(__DIR__ . '/Fixtures/ExistingImplementations.input.php')
                ->actual,
        );
    }
}
