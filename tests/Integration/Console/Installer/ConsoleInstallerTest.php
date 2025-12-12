<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Installer;

use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ConsoleInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                $this->internalStorage . '/install',
                new Psr4Namespace('App\\', $this->internalStorage . '/install/App'),
            )
            ->setRoot($this->internalStorage . '/install');
    }

    protected function tearDown(): void
    {
        $this->installer->clean();

        parent::tearDown();
    }

    public function test_console_installer(): void
    {
        $this->console->call('install console -f');

        $this->installer
            ->assertFileExists('tempest')
            ->assertCommandExecuted('composer up');
    }
}
