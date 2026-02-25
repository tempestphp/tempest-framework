<?php

declare(strict_types=1);

namespace Tempest\View;

use Closure;
use Tempest\Support\Filesystem;
use Throwable;

use function Tempest\internal_storage_path;
use function Tempest\Support\path;

final class ViewCache
{
    public function __construct(
        public bool $enabled = false,
        private ?ViewCachePool $pool = null,
    ) {
        $this->pool ??= new ViewCachePool(
            directory: self::getCachePath(),
        );
    }

    public static function create(bool $enabled = true, ?string $path = null): self
    {
        return new self(
            enabled: $enabled,
            pool: new ViewCachePool($path ?? self::getCachePath()),
        );
    }

    public function clear(): void
    {
        $this->pool->clear();
    }

    public function getCachedViewPath(string $path, Closure $compiledView): string
    {
        $cacheKey = hash('xxh64', $path);

        $cacheItem = $this->pool->getItem($cacheKey);

        if ($this->enabled === false || $cacheItem->isHit() === false) {
            $cacheItem->set($compiledView());

            $this->pool->save($cacheItem);
        }

        return path($this->pool->directory, $cacheItem->getKey() . '.php')->toString();
    }

    /**
     * @param array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}> $lineMap
     */
    public function saveSourceMap(string $compiledViewPath, ?string $sourcePath, array $lineMap): void
    {
        $sourceMapPath = $this->getSourceMapPath($compiledViewPath);

        $sourceMap = [
            'sourcePath' => $sourcePath,
            'lineMap' => $lineMap,
        ];

        Filesystem\write_file(
            $sourceMapPath,
            sprintf("<?php\n\nreturn %s;\n", var_export($sourceMap, true)),
        );
    }

    /**
     * @return array{sourcePath: string|null, lineMap: array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath: string, sourceStartLine: int}>}|null
     */
    public function getSourceMap(string $compiledViewPath): ?array
    {
        $sourceMapPath = $this->getSourceMapPath($compiledViewPath);

        if (! Filesystem\is_file($sourceMapPath)) {
            return null;
        }

        $sourceMap = include $sourceMapPath;

        if (! is_array($sourceMap)) {
            return null;
        }

        return $sourceMap;
    }

    private function getSourceMapPath(string $compiledViewPath): string
    {
        if (str_ends_with($compiledViewPath, '.php')) {
            return substr($compiledViewPath, offset: 0, length: -4) . '.map.php';
        }

        return $compiledViewPath . '.map.php';
    }

    private static function getCachePath(): string
    {
        try {
            return internal_storage_path('cache/views');
        } catch (Throwable) {
            return __DIR__ . '/../.tempest/cache';
        }
    }
}
