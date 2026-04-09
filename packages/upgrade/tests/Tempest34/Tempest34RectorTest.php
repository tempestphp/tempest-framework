<?php

namespace Tempest\Upgrade\Tests\Tempest34;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

#[RunTestsInSeparateProcesses]
final class Tempest34RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest34_rector.php');
    }

    public function test_discovery_cache_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/DiscoveryCacheNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\DiscoveryCache;')
            ->assertNotContains('use Tempest\Core\DiscoveryCache;');
    }

    public function test_discovery_cache_strategy_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/DiscoveryCacheStrategyNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\DiscoveryCacheStrategy;')
            ->assertNotContains('use Tempest\Core\DiscoveryCacheStrategy;');
    }

    public function test_composer_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ComposerNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\Composer;')
            ->assertNotContains('use Tempest\Core\Composer;');
    }

    public function test_composer_json_could_not_be_located_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/ComposerJsonCouldNotBeLocatedNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\ComposerJsonCouldNotBeLocated;')
            ->assertNotContains('use Tempest\Core\ComposerJsonCouldNotBeLocated;');
    }

    public function test_could_not_store_discovery_cache_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/CouldNotStoreDiscoveryCacheNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\CouldNotStoreDiscoveryCache;')
            ->assertNotContains('use Tempest\Core\CouldNotStoreDiscoveryCache;');
    }

    public function test_discovery_caching_strategy_was_changed_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/DiscoveryCachingStrategyWasChangedNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\DiscoveryCachingStrategyWasChanged;')
            ->assertNotContains('use Tempest\Core\DiscoveryCachingStrategyWasChanged;');
    }

    public function test_discovery_config_namespace_change(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/DiscoveryConfigNamespaceChange.input.php')
            ->assertContains('use Tempest\Discovery\DiscoveryConfig;')
            ->assertNotContains('use Tempest\Core\DiscoveryConfig;');
    }

    public function test_fully_qualified_discovery_cache(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/FullyQualifiedDiscoveryCache.input.php')
            ->assertContains('Tempest\Discovery\DiscoveryCache')
            ->assertNotContains('Tempest\Core\DiscoveryCache');
    }

    public function test_kernel_discovery_locations_refactored(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/KernelDiscoveryLocations.input.php')
            ->assertContains('$this->kernel->discoveryConfig->locations')
            ->assertNotContains('$this->kernel->discoveryLocations');
    }

    public function test_kernel_discovery_classes_refactored(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/KernelDiscoveryClasses.input.php')
            ->assertContains('$this->kernel->discoveryConfig->classes')
            ->assertNotContains('$this->kernel->discoveryClasses');
    }
}
