<?php

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\AuthenticatableDiscovery;
use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Database\PrimaryKey;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Reflection\ClassReflector;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AuthenticatableDiscoveryTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function discovers_authenticatable_class(): void
    {
        $config = new AuthConfig();

        $discovery = new AuthenticatableDiscovery($config);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discover(new DiscoveryLocation('', ''), new ClassReflector(Device::class));
        $discovery->apply();

        $this->assertSame(Device::class, $config->authenticatable);
    }
}

final class Device implements CanAuthenticate
{
    public PrimaryKey $id;
}
