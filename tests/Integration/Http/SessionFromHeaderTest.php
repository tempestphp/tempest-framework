<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\DateTime\Duration;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Resolvers\HeaderSessionIdResolver;
use Tempest\Http\Session\Session;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionFromHeaderTest extends FrameworkIntegrationTestCase
{
    public function test_resolving_session_from_header(): void
    {
        $this->container->config(new FileSessionConfig(
            path: 'test_sessions',
            expiration: Duration::hours(2),
            sessionIdResolver: HeaderSessionIdResolver::class,
        ));

        $this->setSessionId('session_a');
        $sessionA = $this->container->get(Session::class);
        $sessionA->set('test', 'a');

        $sessionA = $this->container->get(Session::class);
        $this->assertEquals('a', $sessionA->get('test'));
    }

    private function setSessionId(string $id): void
    {
        $request = new GenericRequest(Method::GET, '/', [], ['tempest_session_id' => $id]);

        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton(GenericRequest::class, fn () => $request);
    }
}
