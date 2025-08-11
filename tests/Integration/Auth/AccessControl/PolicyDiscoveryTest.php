<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\AccessControl;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Auth\AccessControl\AccessDecision;
use Tempest\Auth\AccessControl\Policy;
use Tempest\Auth\AccessControl\PolicyDiscovery;
use Tempest\Auth\AuthConfig;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Reflection\ClassReflector;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use UnitEnum;

final class PolicyDiscoveryTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function can_discover_policy_classes(): void
    {
        $authConfig = new AuthConfig();
        $discovery = new PolicyDiscovery($authConfig);
        $discovery->setItems(new DiscoveryItems());
        $discovery->discover(new DiscoveryLocation('test', __DIR__), new ClassReflector(TestPolicy::class));
        $discovery->apply();

        $this->assertContains(TestPolicy::class, $authConfig->policies);
    }
}

final class TestPolicy implements Policy
{
    public string $model = '';

    public function check(UnitEnum|string $action, ?object $resource, ?object $subject): bool|AccessDecision
    {
        return true;
    }
}
