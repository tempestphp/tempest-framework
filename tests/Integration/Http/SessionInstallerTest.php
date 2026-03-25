<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Session\Installer\SessionInstaller;
use Tempest\Support\Namespace\Psr4Namespace;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionInstallerTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->installer
            ->configure(__DIR__ . '/install', new Psr4Namespace('App\\', __DIR__ . '/install/App'))
            ->setRoot(__DIR__ . '/install');
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        $this->installer->clean();
    }

    #[Test]
    public function installs_database_session_config_and_migration(): void
    {
        $this->console
            ->call(sprintf('install %s', SessionInstaller::class))
            ->input(1)
            ->input(0)
            ->confirm()
            ->input('App/Sessions/session.config.php')
            ->confirm()
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/Sessions/CreateSessionsTable.php')
            ->assertFileExists('App/Sessions/session.config.php');

        $this->installer->assertFileContains('App/Sessions/session.config.php', 'DatabaseSessionConfig');
    }

    #[Test]
    public function installs_file_session_config(): void
    {
        $this->console
            ->call(sprintf('install %s', SessionInstaller::class))
            ->input(0)
            ->input(0)
            ->input('App/Sessions/session.config.php')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/Sessions/session.config.php')
            ->assertFileContains('App/Sessions/session.config.php', 'FileSessionConfig');
    }

    #[Test]
    public function installs_redis_session_config(): void
    {
        $this->console
            ->call(sprintf('install %s', SessionInstaller::class))
            ->input(2)
            ->input('App/Sessions/session.config.php')
            ->assertSuccess();

        $this->installer
            ->assertFileExists('App/Sessions/session.config.php')
            ->assertFileContains('App/Sessions/session.config.php', 'RedisSessionConfig')
            ->assertFileContains('App/Sessions/session.config.php', 'expiration: Duration::days(30),');

        $this->installer
            ->assertFileContains('App/Sessions/session.config.php', 'return new RedisSessionConfig(')
            ->assertFileNotContains('App/Sessions/session.config.php', 'cleanupStrategy:');

        $this->assertFileDoesNotExist($this->installer->path('App/Sessions/CleanupSessionsCommand.php'));
    }

    #[Test]
    public function publishes_cleanup_command_when_requested(): void
    {
        $this->console
            ->call(sprintf('install %s', SessionInstaller::class))
            ->input(0)
            ->input(2)
            ->input('App/Sessions/session.config.php')
            ->confirm()
            ->input('App/Sessions/CleanupSessionsCommand.php')
            ->assertSuccess();

        $this->installer->assertFileExists('App/Sessions/CleanupSessionsCommand.php');
    }

    #[Test]
    public function writes_selected_cleanup_strategy_to_the_config(): void
    {
        $this->console
            ->call(sprintf('install %s', SessionInstaller::class))
            ->input(0)
            ->input(1)
            ->input('App/Sessions/session.config.php')
            ->assertSuccess();

        $this->installer->assertFileContains('App/Sessions/session.config.php', 'cleanupStrategy: CleanupStrategy::EVERY_REQUEST');
    }
}
