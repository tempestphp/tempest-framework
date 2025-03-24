<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Router\Session\SessionConfig;
use Tempest\Router\Session\SessionId;
use Tempest\Router\Session\SessionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\internal_storage_path;

/**
 * @internal
 */
final class CleanupSessionsCommandTest extends FrameworkIntegrationTestCase
{
    public function test_destroy_sessions(): void
    {
        @unlink(internal_storage_path('/tests/sessions/session_a'));
        @unlink(internal_storage_path('/tests/sessions/session_b'));

        $clock = $this->clock('2024-01-01 00:00:00');

        $this->container->config(new SessionConfig(
            path: 'tests/sessions',
            expirationInSeconds: 10,
        ));

        $sessionManager = $this->container->get(SessionManager::class);

        $sessionManager->set(new SessionId('session_a'), 'test', 'value');

        $clock->changeTime(9);

        $sessionManager->set(new SessionId('session_b'), 'test', 'value');

        $clock->changeTime(2);

        $this->console
            ->call('session:clean')
            ->assertContains('session_a')
            ->assertDoesNotContain('session_b');

        $this->assertFileDoesNotExist(internal_storage_path('/tests/sessions/session_a'));
        $this->assertFileExists(internal_storage_path('/tests/sessions/session_b'));
    }
}
