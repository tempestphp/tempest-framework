<?php

namespace Tests\Tempest\Integration\Auth\Authentication;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AuthConfig;
use Tempest\Auth\AuthenticatableDiscovery;
use Tempest\Auth\Authentication\Authenticatable;
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
        $discovery->discover(new DiscoveryLocation('', ''), new ClassReflector(AuthenticatableDevice::class));
        $discovery->discover(new DiscoveryLocation('', ''), new ClassReflector(AuthenticatableUser::class));
        $discovery->apply();

        $this->assertContains(AuthenticatableDevice::class, $config->authenticatables);
        $this->assertContains(AuthenticatableUser::class, $config->authenticatables);
    }
}

final class AuthenticatableDevice implements Authenticatable
{
    public PrimaryKey $id;
}

final class AuthenticatableUser implements Authenticatable
{
    public PrimaryKey $id;
}
