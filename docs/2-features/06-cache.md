---
title: Cache
description: "The cache component is based on Symfony's Cache, providing access to many different adapters through a convenient, simple interface."
---

## Getting started

By default, Tempest uses a filesystem-based caching strategy. You may use a different cache back-end by creating a configuration file for the desired cache adapter.

<!-- For instance, you may use Redis as your cache back-end by creating a `cache.config.php` file returning an instance of {b`Tempest\Cache\Config\RedisCacheConfig`}:

```php app/cache.config.php
return new RedisCacheConfig(
    host: env('REDIS_HOST', default: '127.0.0.1'),
    port: env('REDIS_PORT', default: 6379),
    username: env('REDIS_USERNAME'),
    password: env('REDIS_PASSWORD'),
);
```

In this example, the Redis credentials are specified in the `.env`, so a different bucket and credentials can be configured depending on the environment. Of course, you may use different, more specific environment variables if needed. -->

Once your cache is configured, you may interact with it by using the {`Tempest\Cache\Cache`} interface. This is usually done through [dependency injection](../1-essentials/05-container.md#injecting-dependencies):

```php app/OrderService.php
use Tempest\Cache\Cache;
use Tempest\DateTime\Duration;

final readonly class OrderService
{
    public function __construct(
        private Cache $cache,
    ) {}

    public function getOrdersCount(): int
    {
        return $this->cache->resolve(
            key: 'orders_count',
            callback: fn () => $this->fetchOrdersCountFromDatabase(),
            expiration: Duration::hours(12)
        );
    }

    // …
}
```

## The cache interface

Once you have access to the the {b`Tempest\Cache\Cache`} interface, you gain access to a few useful methods for working with cache items. All methods are documented, so you are free to explore the source to get an understanding of what you can do with it.

Below are a few useful methods that you may need more often than the others:

```php
/**
 * Gets a value from the cache by the given key.
 */
$cache->get($key);

/**
 * Sets a value in the cache for the given key.
 */
$cache->put($key, $value);

/**
 * Gets a value from the cache by the given key, or resolve it using the given callback.
 */
$cache->resolve($key, function () {
    return $this->expensiveOperation();
});
```

## Clearing the cache

The cache may programmatically by cleared by calling the `clear()` method on a cache instance. However, it is sometimes useful to manually clear it. To do so, you may call the `cache:clear` command:

```sh
./tempest cache:clear
```

By default, this would clear the main cache. If there are multiple configured caches, you will be prompted to choose which one to clear.

## Disabling caches

During development, all internal caches except the icon one are disabled. This is to ensure that you always get the latest changes when working on your application.

In production, all caches are automatically enabled without you needing to tweak any configuration. In all environments, you may forcefully enable or disable caches by adding a dedicated environment variable to your `.env`.

### Disabling project caches

You may set the `CACHE_ENABLED` environment variable to `false` to forcefully disable your project cache. When disabled, the cache will not save any value and will return default values for getter methods.

```ini .env
# Force-disables user cache
CACHE_ENABLED=false

# Force-disables a tagged cache named `custom`
CACHE_CUSTOM_ENABLED=false
```

### Disabling internal caches

Tempest has a few internal caches for views, discovery, configuration and icons. You may forcefully disable these caches, individually or all at once, by setting the following environment variables in your `.env` file:

```ini .env
# Force-disables all internal caches
INTERNAL_CACHES=false

# Force-disables the view cache
VIEW_CACHE=false

# Force-disables the icon cache
ICON_CACHE=false

# Force-disables the discovery cache
DISCOVERY_CACHE=false

# Force-disables the config cache
CONFIG_CACHE=false
```

## Locks

You may create a lock by calling the `lock()` method on a cache instance. After being created, the lock needs to be acquired by calling the `acquire()`, and released by calling the `release()` method.

Alternatively, the `execute()` method may be used to acquire a lock, execute a callback, and release the lock automatically when the callback is done.

```php
// Create the lock
$lock = $cache->lock('processing', Duration::seconds(30));

// Acquire the lock, do something and release it.
if ($lock->acquire()) {
    $this->process();

    $lock->release();
}

// Or using a callback, with an optional wait
// time if the lock is not yet available.
$lock->execute($this->process(...), wait: Duration::seconds(30));
```

### Lock ownership

Normally, a lock cannot be acquired if it is already held by another process. However, if you know the owner token, you may still access a lock by specifying the `owner` parameter.

This may be useful to release a lock in an async command, for instance.

```php
$cache->lock("processing:{$processId}", owner: $processId)
    ->release();
```

## Configuration

Tempest provides a different configuration object for each cache provider. Below are the ones that are currently supported:

- {`Tempest\Cache\Config\FilesystemCacheConfig`}
- {`Tempest\Cache\Config\InMemoryCacheConfig`}
- {`Tempest\Cache\Config\PhpCacheConfig`}

<!-- - {`Tempest\Cache\Config\RedisCacheConfig`}
- {`Tempest\Cache\Config\PredisCacheConfig`}
- {`Tempest\Cache\Config\ValkeyCacheConfig`} -->

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the cache testing utilities through the `cache` property.

These utilities include a way to replace the cache with a testing implementation, as well as a few assertion methods related to cache items and locks.

### Faking the cache

You may generate a fake, testing-only cache by calling the `fake()` method on the `cache` property. This will replace the cache implementation in the container, and provide useful assertion methods.

```php
// Replace the cache with a fake implementation
$cache = $this->cache->fake();

// Asserts that the specified cache key exists
$cache->assertCached('users_count');

// Asserts that the cache is empty
$cache->assertEmpty();
```

### Testing locks

Calling the `lock()` method on the cache testing utility will return a testing lock, which provides a few more testing utilities.

```php
$cache = $this->cache->fake();

// Call some application code
// …

$cache->assertNotLocked('processing');
```
