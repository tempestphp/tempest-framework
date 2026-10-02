<?php

namespace Tempest\Upgrade\Tests\Tempest320;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest320RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest320_rector.php');
    }

    #[Test]
    public function session_implementation_methods_are_added(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/SessionImplementations.input.php')
            ->assertContains('public function regenerate(Session $session): void')
            ->assertContains('public function issueNewId(): SessionId')
            ->assertContains("throw new BadMethodCallException('regenerate() is not implemented yet.');")
            ->assertContains("throw new BadMethodCallException('issueNewId() is not implemented yet.');");
    }

    #[Test]
    public function aliased_session_implementations_are_updated(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/AliasedSessionImplementations.input.php')
            ->assertContains('public function regenerate(Session $session): void')
            ->assertContains('public function issueNewId(): SessionId');
    }

    #[Test]
    public function existing_session_methods_are_not_overwritten(): void
    {
        $this->assertSame(
            '',
            $this->rector
                ->runFixture(__DIR__ . '/Fixtures/ExistingSessionImplementations.input.php')
                ->actual,
        );
    }
}
