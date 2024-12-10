<?php

namespace Integration\Console\Installer;

use Tempest\Core\ComposerNamespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ConsoleInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                __DIR__ . '/install',
                new ComposerNamespace('App\\', __DIR__ . '/install/App'),
            )
            ->setRoot(__DIR__ . '/install');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_console_installer(): void
    {
        $this->console
            ->call('install console -f');

        $this->installer
            ->assertFileExists('tempest');
    }
}
