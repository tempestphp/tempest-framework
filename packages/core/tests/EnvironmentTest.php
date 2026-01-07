<?php

namespace Tempest\Core\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Core\Environment;
use Tempest\Core\EnvironmentInitializer;
use Tempest\Core\EnvironmentValueWasInvalid;

final class EnvironmentTest extends TestCase
{
    #[Test]
    public function default_is_local(): void
    {
        putenv('ENVIRONMENT=null');

        $this->assertSame(Environment::LOCAL, Environment::guessFromEnvironment());
    }

    #[Test]
    public function throws_on_unknown_value(): void
    {
        putenv('ENVIRONMENT=unknown');

        $this->expectException(EnvironmentValueWasInvalid::class);

        Environment::guessFromEnvironment();
    }

    #[Test]
    public function can_be_resolved_from_container(): void
    {
        $container = new GenericContainer();
        $container->addInitializer(EnvironmentInitializer::class);

        putenv('ENVIRONMENT=staging');
        $this->assertSame(Environment::STAGING, $container->get(Environment::class));

        // ensure it's a singleton
        putenv('ENVIRONMENT=production');
        $this->assertSame(Environment::STAGING, $container->get(Environment::class));
    }
}
