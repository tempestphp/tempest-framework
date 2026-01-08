<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\CleanupSessionsCommand;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\internal_storage_path;

/**
 * @internal
 */
final class CleanupSessionsCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function destroy_sessions(): void
    {
        Filesystem\delete(internal_storage_path('/tests/sessions/'));

        $clock = $this->clock('2024-01-01 00:00:00');

        $this->container->config(new FileSessionConfig(
            path: 'tests/sessions',
            expiration: Duration::seconds(10),
        ));

        $sessionManager = $this->container->get(SessionManager::class);

        $sessionA = $sessionManager->getOrCreate(new SessionId('session_a'));
        $sessionA->set('test', 'value');

        $sessionManager->save($sessionA);

        $clock->plus(Duration::seconds(9));

        $sessionB = $sessionManager->getOrCreate(new SessionId('session_b'));
        $sessionB->set('test', 'value');

        $sessionManager->save($sessionB);

        $clock->plus(Duration::seconds(2));

        $this->console
            ->call(CleanupSessionsCommand::class)
            ->assertContains('session_a')
            ->assertDoesNotContain('session_b');

        $this->assertFileDoesNotExist(internal_storage_path('/tests/sessions/session_a'));
        $this->assertFileExists(internal_storage_path('/tests/sessions/session_b'));
    }
}
