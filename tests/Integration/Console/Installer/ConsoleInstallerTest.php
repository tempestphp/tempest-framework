<?php

declare(strict_types=1);

namespace Integration\Console\Installer;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ConsoleInstallerTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->installer
            ->configure(
                __DIR__ . '/install',
                new Psr4Namespace('App\\', __DIR__ . '/install/App'),
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
        $this->console->call('install console -f');

        $this->installer
            ->assertFileExists('tempest')
            ->assertCommandExecuted('composer up');
    }
}
