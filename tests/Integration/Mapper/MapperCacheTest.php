<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Container\GenericContainer;
use Tempest\Mapper\MapperCache;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Mapper\MappingContext;
use Tempest\Mapper\ObjectFactory;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\MapperPrefix;
use Tests\Tempest\Integration\Mapper\Fixtures\PrefixedString;
use Tests\Tempest\Integration\Mapper\Fixtures\PrefixedStringMapper;

/**
 * @internal
 */
final class MapperCacheTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function resolves_mappers_once_per_context(): void
    {
        $cache = new MapperCache();
        $resolved = 0;

        $resolve = function () use (&$resolved): array {
            $resolved++;

            return [$this->container->get(ArrayToObjectMapper::class, context: MappingContext::default())];
        };

        $first = $cache->resolve(new MappingContext('default'), $resolve);
        $second = $cache->resolve(new MappingContext('default'), $resolve);

        $this->assertSame(1, $resolved);
        $this->assertSame($first, $second);

        $cache->resolve(new MappingContext('other'), $resolve);

        $this->assertSame(2, $resolved);
    }

    #[Test]
    public function is_reset_between_worker_requests(): void
    {
        $cache = $this->container->get(MapperCache::class);
        $resolved = 0;

        $resolve = function () use (&$resolved): array {
            $resolved++;

            return [$this->container->get(ArrayToObjectMapper::class, context: MappingContext::default())];
        };

        $cache->resolve(new MappingContext('default'), $resolve);
        $cache->resolve(new MappingContext('default'), $resolve);

        $this->assertSame(1, $resolved);

        $this->container->reset();

        // A reset clears the cache without dropping the singleton. The mappers must therefore be
        // re-resolved through the same instance.
        $this->assertSame($cache, $this->container->get(MapperCache::class));

        $cache->resolve(new MappingContext('default'), $resolve);

        $this->assertSame(2, $resolved);
    }

    #[Test]
    public function mappers_are_not_shared_between_containers(): void
    {
        $container = function (string $prefix): GenericContainer {
            $container = new GenericContainer();
            $container->singleton(MapperPrefix::class, new MapperPrefix($prefix));
            $container->singleton(MapperConfig::class, new MapperConfig([PrefixedStringMapper::class]));

            return $container;
        };

        $a = $container('a:')->get(ObjectFactory::class);
        $b = $container('b:')->get(ObjectFactory::class);

        $this->assertSame('a:value', $a->withData('value')->to(PrefixedString::class)->value);
        $this->assertSame('b:value', $b->withData('value')->to(PrefixedString::class)->value);
    }
}
