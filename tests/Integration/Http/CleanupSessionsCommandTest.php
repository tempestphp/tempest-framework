<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
final class CleanupSessionsCommandTest extends IntegrationTest
{
    public function test_destroy_sessions(): void
    {
        @unlink(__DIR__ . '/sessions/session_a');
        @unlink(__DIR__ . '/sessions/session_b');

        $clock = $this->clock('2024-01-01 00:00:00');

        $this->container->config(new SessionConfig(
            path: __DIR__ . '/sessions',
            expirationInSeconds: 10,
        ));

        $sessionManager = $this->container->get(SessionManager::class);

        $sessionManager->put(new SessionId('session_a'), 'test', 'value');

        $clock->changeTime(9);

        $sessionManager->put(new SessionId('session_b'), 'test', 'value');

        $clock->changeTime(2);

        $this->console
            ->call('session:clean')
            ->assertContains('session_a')
            ->assertDoesNotContain('session_b');

        $this->assertFileDoesNotExist(__DIR__ . '/sessions/session_a');
        $this->assertFileExists(__DIR__ . '/sessions/session_b');
    }
}
