<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use Tempest\Cache\CacheConfig;
use Tempest\View\ViewCache;
use Tempest\View\ViewCachePool;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\path;

/**
 * @internal
 */
final class ViewCacheTest extends FrameworkIntegrationTestCase
{
    private const string DIRECTORY = __DIR__ . '/.cache';

    private CacheConfig $cacheConfig;

    private ViewCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheConfig = new CacheConfig();

        $this->cache = new ViewCache(
            $this->cacheConfig,
            new ViewCachePool(
                directory: self::DIRECTORY,
            ),
        );
    }

    protected function tearDown(): void
    {
        $directory = path(self::DIRECTORY);

        if ($directory->isDirectory()) {
            /** @phpstan-ignore-next-line */
            $directory->glob('/*.php')->each(fn (string $file) => unlink($file));

            rmdir(self::DIRECTORY);
        }

        parent::tearDown();
    }

    public function test_view_cache(): void
    {
        $path = $this->cache->getCachedViewPath('path', fn () => 'hi');

        $this->assertFileExists($path);
        $this->assertSame('hi', file_get_contents($path));
    }

    public function test_view_cache_when_disabled(): void
    {
        $hit = 0;

        $this->cacheConfig->enable = false;

        $compileFunction = function () use (&$hit) {
            $hit += 1;

            return 'hi';
        };

        $this->cache->getCachedViewPath('path', $compileFunction);
        $path = $this->cache->getCachedViewPath('path', $compileFunction);

        $this->assertFileExists($path);
        $this->assertSame('hi', file_get_contents($path));
        $this->assertSame(2, $hit);
    }

    public function test_view_cache_when_enabled(): void
    {
        $hit = 0;

        $this->cacheConfig->enable = true;

        $compileFunction = function () use (&$hit) {
            $hit += 1;

            return 'hi';
        };

        $this->cache->getCachedViewPath('path', $compileFunction);
        $path = $this->cache->getCachedViewPath('path', $compileFunction);

        $this->assertFileExists($path);
        $this->assertSame('hi', file_get_contents($path));
        $this->assertSame(1, $hit);
    }
}
