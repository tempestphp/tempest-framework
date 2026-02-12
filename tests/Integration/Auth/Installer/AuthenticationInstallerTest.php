<?php

namespace Integration\Auth\Installer;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AuthenticationInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                __DIR__ . '/install',
                new Psr4Namespace('App\\', __DIR__ . '/install/App'),
            )
            ->setRoot(__DIR__ . '/install')
            ->put('.env.example', '')
            ->put('.env', '');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    #[Test]
    public function install_oauth_provider_with_migrations(): void
    {
        $this->console
            ->call('install auth --force --migrate')
            ->input(0)
            ->assertSuccess();
    }
}
