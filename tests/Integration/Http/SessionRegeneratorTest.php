<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionManager;
use Tempest\Http\Session\SessionRegenerator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionRegeneratorTest extends FrameworkIntegrationTestCase
{
    private Session $session {
        get => $this->container->get(Session::class);
    }

    private SessionRegenerator $regenerator {
        get => $this->container->get(SessionRegenerator::class);
    }

    #[Test]
    public function assigns_a_new_identifier_and_keeps_data(): void
    {
        $this->session->set('key', 'value');
        $previousId = (string) $this->session->id;

        $this->regenerator->regenerate();

        $this->assertNotSame($previousId, (string) $this->session->id);
        $this->assertSame('value', $this->session->get('key'));
    }

    #[Test]
    public function invalidate_discards_data(): void
    {
        $this->session->set('key', 'value');

        $this->regenerator->invalidate();

        $this->assertNull($this->session->get('key'));
    }

    #[Test]
    public function destroys_the_session_it_replaces(): void
    {
        $sessionManager = $this->container->get(SessionManager::class);

        $this->session->set('key', 'value');
        $previousId = $this->session->id;

        $this->regenerator->regenerate();

        $previousSession = $sessionManager->getOrCreate($previousId);

        $this->assertNull($previousSession->get('key'));
    }

    #[Test]
    public function sends_the_new_identifier_to_the_client(): void
    {
        $cookies = $this->container->get(CookieManager::class);

        $this->regenerator->regenerate();

        $this->assertSame(
            (string) $this->session->id,
            $cookies->get('tempest_session_id')?->value,
        );
    }
}
