<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\AccessControl;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AccessControl\AccessControl;
use Tempest\Auth\AccessControl\AccessControlInitializer;
use Tempest\Auth\AccessControl\PolicyBasedAccessControl;
use Tempest\Auth\AuthConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class AccessControlInitializerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function initializes_policy_based_access_control(): void
    {
        $this->container->config(new AuthConfig());

        $initializer = new AccessControlInitializer();
        $accessControl = $initializer->initialize($this->container);

        $this->assertInstanceOf(PolicyBasedAccessControl::class, $accessControl);
    }

    #[Test]
    public function access_control_is_registered_as_singleton(): void
    {
        $this->container->config(new AuthConfig());

        $accessControl1 = $this->container->get(AccessControl::class);
        $accessControl2 = $this->container->get(AccessControl::class);

        $this->assertSame($accessControl1, $accessControl2);
    }
}
