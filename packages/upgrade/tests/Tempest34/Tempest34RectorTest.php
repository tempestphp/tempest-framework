<?php

namespace Tempest\Upgrade\Tests\Tempest34;

use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

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
}
