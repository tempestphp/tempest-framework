<?php

namespace Tempest\View;

use Closure;
use Exception;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\CacheItem;
use function Tempest\Support\arr;

final readonly class ViewCachePool implements CacheItemPoolInterface
{
    public function getItem(string $key): CacheItemInterface
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
            newScope: CacheItem::class
        );

        return $createCacheItem($key, $this->makePath($key), $this->hasItem($key));
    }

    public function getItems(array $keys = []): iterable
    {
        return arr($keys)
            ->map(fn (string $key) => $this->getItem($key));
    }

    public function hasItem(string $key): bool
    {
        return file_exists($this->makePath($key));
    }

    public function clear(): bool
    {
        arr(glob(__DIR__ . '/.cache/*.php'))
            ->each(fn (string $path) => $this->deleteItem(pathinfo($path, PATHINFO_FILENAME)));

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
    }

    public function save(CacheItemInterface $item): bool
    {
        file_put_contents($this->makePath($item), $item->get());

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        // TODO: Implement saveDeferred() method.
        throw new Exception('No');
    }

    public function commit(): bool
    {
        // TODO: Implement commit() method.
        throw new Exception('No');
    }

    private function makePath(CacheItemInterface|string $key): string
    {
        $key = is_string($key) ? $key : $key->getKey();

        return __DIR__ . "/.cache/{$key}.php";
    }
}