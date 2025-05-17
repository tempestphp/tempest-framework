<?php

declare(strict_types=1);

namespace Tempest\View;

use Closure;
use Exception;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;
use Tempest\Support\Arr\ImmutableArray;

use function Tempest\Support\arr;
use function Tempest\Support\path;

final readonly class ViewCachePool implements CacheItemPoolInterface
{
    public function __construct(
        public string $directory,
    ) {}

    public function getItem(string $key): CacheItem
    {
        $createCacheItem = Closure::bind(
            closure: static function ($key, $value, $isHit) {
                $item = new CacheItem();
                $item->key = $key;
                $item->isTaggable = true;
                $item->isHit = $isHit;
                $item->value = $value;

                return $item;
            },
            newThis: null,
            newScope: CacheItem::class,
        );

        return $createCacheItem($key, $this->makePath($key), $this->hasItem($key));
    }

    /**
     * @return ImmutableArray<array-key, \Psr\Cache\CacheItemInterface>
     */
    public function getItems(array $keys = []): ImmutableArray
    {
        return arr($keys)->map(fn (string $key) => $this->getItem($key));
    }

    public function hasItem(string $key): bool
    {
        return file_exists($this->makePath($key));
    }

    public function clear(): bool
    {
        $path = path($this->directory);

        if ($path->isDirectory()) {
            /** @phpstan-ignore-next-line  */
            $path->glob('/*.php')->each(fn (string $file) => unlink($file));

            rmdir($this->directory);
        }

        return true;
    }

    public function deleteItem(string $key): bool
    {
        @unlink($this->makePath($key));

        return true;
    }

    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->deleteItem($key);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $path = $this->makePath($item);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        file_put_contents($path, $item->get());

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        throw new Exception('Not supported');
    }

    public function commit(): bool
    {
        throw new Exception('Not supported');
    }

    private function makePath(CacheItemInterface|string $key): string
    {
        $key = is_string($key) ? $key : $key->getKey();

        return path($this->directory, "/{$key}.php")->toString();
    }
}
