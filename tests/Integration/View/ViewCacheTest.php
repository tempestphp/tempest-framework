<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\View;

use PHPUnit\Framework\Attributes\Test;
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

    private ViewCache $viewCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewCache = new ViewCache(
            enabled: true,
            pool: new ViewCachePool(
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

        putenv('ENVIRONMENT=testing');
        putenv('VIEW_CACHE=');
        putenv('INTERNAL_CACHES=');

        parent::tearDown();
    }

    #[Test]
    public function enabled_by_default_in_production(): void
    {
        putenv('ENVIRONMENT=production');

        $this->container->unregister(ViewCache::class);

        $this->assertTrue($this->container->get(ViewCache::class)->enabled);
    }

    #[Test]
    public function enabled_by_default_in_staging(): void
    {
        putenv('ENVIRONMENT=staging');

        $this->container->unregister(ViewCache::class);

        $this->assertTrue($this->container->get(ViewCache::class)->enabled);
    }

    #[Test]
    public function disabled_by_default_locally(): void
    {
        putenv('ENVIRONMENT=local');

        $this->container->unregister(ViewCache::class);

        $this->assertFalse($this->container->get(ViewCache::class)->enabled);
    }

    #[Test]
    public function overriden_by_internal_caches_in_production(): void
    {
        putenv('ENVIRONMENT=production');
        putenv('INTERNAL_CACHES=false');

        $this->container->unregister(ViewCache::class);

        $this->assertFalse($this->container->get(ViewCache::class)->enabled);
    }

    #[Test]
    public function overriden_by_view_cache_locally(): void
    {
        putenv('ENVIRONMENT=local');
        putenv('VIEW_CACHE=true');

        $this->container->unregister(ViewCache::class);

        $this->assertTrue($this->container->get(ViewCache::class)->enabled);
    }

    public function test_view_cache(): void
    {
        $path = $this->viewCache->getCachedViewPath('path', fn () => 'hi');

        $this->assertFileExists($path);
        $this->assertSame('hi', file_get_contents($path));
    }

    public function test_view_cache_when_disabled(): void
    {
        $hit = 0;

        $this->viewCache->enabled = false;

        $compileFunction = function () use (&$hit) {
            $hit += 1;

            return 'hi';
        };

        $this->viewCache->getCachedViewPath('path', $compileFunction);
        $path = $this->viewCache->getCachedViewPath('path', $compileFunction);

        $this->assertFileExists($path);
        $this->assertSame('hi', file_get_contents($path));
        $this->assertSame(2, $hit);
    }

    public function test_view_cache_when_enabled(): void
    {
        $hit = 0;

        $this->viewCache->enabled = true;

        $compileFunction = function () use (&$hit) {
            $hit += 1;

            return 'hi';
        };

        $this->viewCache->getCachedViewPath('path', $compileFunction);
        $path = $this->viewCache->getCachedViewPath('path', $compileFunction);

        $this->assertFileExists($path);
        $this->assertSame('hi', file_get_contents($path));
        $this->assertSame(1, $hit);
    }
}
