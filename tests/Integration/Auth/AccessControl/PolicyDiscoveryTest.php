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

final class PolicyDiscoveryTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function discovers_policy_methods(): void
    {
        $config = new AuthConfig();

        $discovery = new PolicyDiscovery($config);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discover(new DiscoveryLocation('', ''), new ClassReflector(TestPolicyClass::class));
        $discovery->apply();

        $this->assertArrayHasKey(TestModel::class, $config->policies);
        $this->assertArrayHasKey('view', $config->policies[TestModel::class]);
        $this->assertArrayHasKey('edit', $config->policies[TestModel::class]);
        $this->assertCount(1, $config->policies[TestModel::class]['view']);
        $this->assertCount(1, $config->policies[TestModel::class]['edit']);
    }

    #[Test]
    public function discovers_policy_methods_with_multiple_actions(): void
    {
        $config = new AuthConfig();

        $discovery = new PolicyDiscovery($config);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discover(new DiscoveryLocation('', ''), new ClassReflector(TestPolicyWithMultipleActions::class));
        $discovery->apply();

        $this->assertArrayHasKey(TestModel::class, $config->policies);
        $this->assertArrayHasKey('create', $config->policies[TestModel::class]);
        $this->assertArrayHasKey('update', $config->policies[TestModel::class]);
        $this->assertCount(1, $config->policies[TestModel::class]['create']);
        $this->assertCount(1, $config->policies[TestModel::class]['update']);
    }

    #[Test]
    public function discovers_policy_methods_with_enum_actions(): void
    {
        $config = new AuthConfig();

        $discovery = new PolicyDiscovery($config);
        $discovery->setItems(new DiscoveryItems([]));
        $discovery->discover(new DiscoveryLocation('', ''), new ClassReflector(TestPolicyWithEnumActions::class));
        $discovery->apply();

        $this->assertArrayHasKey(TestModel::class, $config->policies);
        $this->assertArrayHasKey('delete', $config->policies[TestModel::class]);
        $this->assertCount(1, $config->policies[TestModel::class]['delete']);
    }
}

final class TestModel
{
    public function __construct(
        public string $name,
    ) {}
}

enum TestAction: string
{
    case DELETE = 'delete';
}

final class TestPolicyClass
{
    #[Policy(TestModel::class, action: 'view')]
    public function canView(): bool
    {
        return true;
    }

    #[Policy(TestModel::class, action: 'edit')]
    public function canEdit(): AccessDecision
    {
        return AccessDecision::granted();
    }
}

final class TestPolicyWithMultipleActions
{
    #[Policy(TestModel::class, action: ['create', 'update'])]
    public function canCreateOrUpdate(): bool
    {
        return true;
    }
}

final class TestPolicyWithEnumActions
{
    #[Policy(TestModel::class, action: TestAction::DELETE)]
    public function canDelete(): bool
    {
        return false;
    }
}
