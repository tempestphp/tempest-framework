<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Http\Method;
use Tempest\Router\GenericRequest;
use Tempest\Router\Request;
use Tempest\Router\Session\Resolvers\HeaderSessionIdResolver;
use Tempest\Router\Session\Session;
use Tempest\Router\Session\SessionConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionFromHeaderTest extends FrameworkIntegrationTestCase
{
    public function test_resolving_session_from_header(): void
    {
        $this->container->config(new SessionConfig(
            path: 'test_sessions',
            idResolverClass: HeaderSessionIdResolver::class,
        ));

        $this->setSessionId('session_a');
        $sessionA = $this->container->get(Session::class);
        $sessionA->set('test', 'a');

        $sessionA = $this->container->get(Session::class);
        $this->assertEquals('a', $sessionA->get('test'));
    }

    private function setSessionId(string $id): void
    {
        $request = new GenericRequest(Method::GET, '/', [], [Session::ID => $id]);

        $this->container->singleton(Request::class, fn () => $request);
        $this->container->singleton(GenericRequest::class, fn () => $request);
    }
}
