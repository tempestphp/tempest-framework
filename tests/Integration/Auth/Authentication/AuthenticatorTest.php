<?php

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\AuthenticatorInitializer;
use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Auth\Authentication\SessionAuthenticator;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AuthenticatorTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function default_authenticator_is_session(): void
    {
        $this->assertInstanceOf(SessionAuthenticator::class, $this->container->get(Authenticator::class));
    }

    #[Test]
    public function can_override_authenticator(): void
    {
        $this->container->removeInitializer(AuthenticatorInitializer::class);
        $this->container->addInitializer(InMemoryAuthenticatorInitializer::class);

        $this->assertInstanceOf(InMemoryAuthenticator::class, $this->container->get(Authenticator::class));
    }
}

final class InMemoryAuthenticator implements Authenticator
{
    private ?CanAuthenticate $authenticatable = null;

    public function authenticate(CanAuthenticate $authenticatable): void
    {
        $this->authenticatable = $authenticatable;
    }

    public function deauthenticate(): void
    {
        $this->authenticatable = null;
    }

    public function current(): ?CanAuthenticate
    {
        return $this->authenticatable;
    }
}

final readonly class InMemoryAuthenticatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): Authenticator
    {
        return new InMemoryAuthenticator();
    }
}
