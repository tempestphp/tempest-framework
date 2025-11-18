<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class DatabaseSessionInstallerTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->installer
            ->configure($this->internalStorage . '/install', new Psr4Namespace('App\\', $this->internalStorage . '/install/App'))
            ->setRoot($this->internalStorage . '/install');
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        $this->installer->clean();
    }

    #[Test]
    public function installer(): void
    {
        $this->console->call('install sessions:database -f --no-migrate');

        $this->installer
            ->assertFileExists('App/Sessions/CreateSessionsTable.php')
            ->assertFileExists('App/Sessions/session.config.php');
    }
}
