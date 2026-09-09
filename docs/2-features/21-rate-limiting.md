---
title: Rate limiting
description: "Limit how often a route may be requested, or throttle any operation, by counting attempts against a key within a window of time."
---

## Overview

The `tempest/rate-limit` package provides a {b`Tempest\RateLimit\RateLimiter`} for throttling any operation, alongside the {b`Tempest\RateLimit\Http\Throttle`} attribute for managing routes.

Counters are stored in the [cache](./06-cache.md) by default, requiring no extra infrastructure out of the box. For high-concurrency production environments, switch to [Redis](#storage) for atomic counting.

## Throttling routes

Add the {b`Tempest\RateLimit\Http\Throttle`} attribute to a controller method:

```php app/PostController.php
use Tempest\RateLimit\Http\Throttle;
use Tempest\Router\Get;

final readonly class PostController
{
    #[Throttle(attempts: 60)]
    #[Get('/api/posts')]
    public function index(): Response
    { /* … */ }
}

```

The window defaults to one minute. To extend it, specify `per` and `every`:

```php
use Tempest\RateLimit\Per;

#[Throttle(attempts: 1000, per: Per::DAY)]
#[Throttle(attempts: 10, per: Per::MINUTE, every: 5)]

```

By default, every route and every client gets an independent counter. To share a limit across multiple routes, assign a common `bucket`:

```php
#[Throttle(attempts: 100, bucket: 'api')]

```

A bucket groups routes, not clients: the routes naming it draw from a single allowance, and that allowance is still counted per client. Routes may name the same bucket with different attempt counts—the narrowest of them decides how much allowance there is, so a single route can tighten the bucket it shares. They are grouped per window, though: limits measuring different spans keep counters of their own, since one counter can only last one span. Unnamed limits are automatically scoped by their exact allowance criteria, meaning attributes can be reordered freely without breaking counters.

Changing an allowance resets its counter, lifting current limits. A named bucket keeps its counter when the attempts change, but not when the window does.

You can also apply `#[Throttle]` directly to a controller class. This applies the allowance globally to all routes exposed by the controller, while method-level limits stack on top to narrow allowances further.

Allowed requests pass through normally with rate limit headers appended:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 58
X-RateLimit-Reset: 1767225600

```

Exceeded limits return a `429 Too Many Requests` status paired with a `Retry-After` header.

Because rate limit headers represent a single client's unique usage, responses carrying them should not be shared via proxy caches. Disable headers entirely by setting `includeHeaders: false` in your rate limit configuration.

### Multiple limits

Because the attribute is repeatable, routes can combine multiple limits, such as pairing a strict burst threshold with a broad daily quota:

```php
#[Throttle(attempts: 20)]
#[Throttle(attempts: 1000, per: Per::DAY)]
#[Get('/api/posts')]
public function index(): Response
{ /* … */ }

```

Limits evaluate sequentially—starting with route-level rules and following up with controller-level rules. Evaluation halts on the first rejection, preventing clients from burning through long-term quotas while spamming short-term burst limits.

## Choosing what to count

Requests default to tracking against the client's IP address via {b`Tempest\RateLimit\Http\ClientIPKeyResolver`}, utilizing packed formats so `::ffff:127.0.0.1` and `127.0.0.1` share a single counter.

Applications behind a reverse proxy must configure trusted proxies in {b`Tempest\Http\Ip\TrustedProxiesConfig`} (see the [trusted proxies documentation](../1-essentials/01-routing.md#trusted-proxies)). Without this, all incoming proxy requests collapse into a single shared counter.

To track limits by authenticated users or API keys instead, implement {b`Tempest\RateLimit\Http\RateLimitKeyResolver`}:

```php app/ApiKeyResolver.php
use Tempest\Http\Request;
use Tempest\RateLimit\Http\RateLimitKeyResolver;

final readonly class ApiKeyResolver implements RateLimitKeyResolver
{
    public function resolve(Request $request): ?string
    {
        return $request->headers->get('x-api-key') ?? $request->ip?->toString();
    }
}

```

Register your resolver in the configuration:

```php app/rateLimit.config.php
use Tempest\RateLimit\Config\CacheRateLimitConfig;

return new CacheRateLimitConfig(
    keyResolverClass: ApiKeyResolver::class,
);

```

Resolvers should return `null` for unidentifiable requests, routing them into a single collective counter so anonymous traffic remains strictly throttled.

## Limits that depend on the request

Dynamic limits—such as granting higher tiers to paying customers while leaving internal traffic unlimited—can be implemented using {b`Tempest\RateLimit\Http\RateLimitProfile`}:

```php app/ApiRateLimitProfile.php
use Tempest\Http\Request;
use Tempest\RateLimit\Http\RateLimitProfile;
use Tempest\RateLimit\Per;
use Tempest\RateLimit\RateLimit;

final readonly class ApiRateLimitProfile implements RateLimitProfile
{
    public function resolve(Request $request): array
    {
        if ($request->headers->get('x-api-key') === null) {
            return [RateLimit::perMinute(20)];
        }

        return [
            RateLimit::perMinute(200),
            RateLimit::perDay(100_000),
        ];
    }
}

```

Reference the profile using the {b`Tempest\RateLimit\Http\ThrottleWith`} attribute:

```php
use Tempest\RateLimit\Http\ThrottleWith;

#[ThrottleWith(ApiRateLimitProfile::class)]
#[Get('/api/posts')]
public function index(): Response
{ /* … */ }

```

Unkeyed profile limits scope like `#[Throttle]` attributes, generating individual counters per route and client. A limit carrying a key is counted under that key exactly as written, with no scoping added on top—so give it a key that identifies what it counts, such as `login:{$email}`. Such a counter is shared by every route naming it, and is the one kind of HTTP counter that can be inspected or cleared through {b`Tempest\RateLimit\RateLimiter`}. Returning an empty array leaves requests completely unlimited.

## Throttling anything else

The limiter operates independently of HTTP. Inject {b`Tempest\RateLimit\RateLimiter`} to protect any background operation, outgoing request, or resource-heavy job:

```php
use Tempest\RateLimit\RateLimit;
use Tempest\RateLimit\RateLimiter;

final readonly class SendVerificationEmail
{
    public function __construct(
        private RateLimiter $limiter,
    ) {}

    public function __invoke(User $user): void
    {
        $limit = RateLimit::perHour(3)->withKey("verification-email:{$user->id}");

        if ($this->limiter->attempt($limit)->exceeded) {
            return;
        }

        // …
    }
}

```

Build limits using `RateLimit::perSecond()`, `perMinute()`, `perHour()`, or `perDay()`, optionally passing a multiplier as the second argument. Use `withKey()` to scope a limit to a key, or `scopedTo()` to append to the key it already has. A limit must carry a key by the time it reaches the limiter—keyless limits throw {b`Tempest\RateLimit\RateLimitKeyWasMissing`} rather than being guessed at, since they would otherwise all share a single counter.

The `attempt()` method records attempts and returns a {b`Tempest\RateLimit\RateLimitResult`}:

```php
$result = $this->limiter->attempt($limit, by: 1);

$result->allowed;    // whether the attempt fits within the limit
$result->exceeded;   // the inverse
$result->limit;      // the maximum amount of attempts
$result->hits;       // attempts made in the current window
$result->remaining;  // attempts left in the current window
$result->retryAfter; // a Duration to wait for, zero when allowed
$result->resetsAt;   // when the window expires

```

Use `peek()` to check limits without incrementing hits, or `clear()` to reset records (such as after a successful login). The `throttle()` method executes callbacks conditionally:

```php
$this->limiter->throttle($limit, function () {
    // …
});

```

Exceeding limits via `throttle()` throws {b`Tempest\RateLimit\RateLimitWasExceeded`} (implementing {b`Tempest\RateLimit\RateLimitException`}), carrying the result payload for clean error handling. Manual limit management gives you direct control over custom domain objects, accounts, or tenants, requiring you to handle rejections explicitly via try-catch blocks or conditional `attempt()` branches.

## Storage

Windows are managed via {b`Tempest\RateLimit\RateLimitStorage`}, which is built by the configured {b`Tempest\RateLimit\Config\RateLimitConfig`}. Tempest defaults to {b`Tempest\RateLimit\Config\CacheRateLimitConfig`}, which requires no external services beyond a standard [cache](./06-cache.md). Because it serialises updates using locks rather than atomic operations, concurrent loads may lead to undercounting. It is also only as durable as the cache itself—when the cache is disabled, no counter is persisted and no limit is ever reached.

Requests for one counter wait for each other, for up to `lockWaitInMilliseconds`. A counter still locked by then cannot be read, so the attempt has no outcome and the request is turned away with a `429`—letting it through would leave the route unmetered exactly when it is under load. Counters are scoped per client, so a client contending with itself is the one held back.

For high-concurrency production environments, switch to {b`Tempest\RateLimit\Config\RedisRateLimitConfig`}, which stores windows in Redis using atomic Lua-script increments:

```php app/rateLimit.config.php
use Tempest\RateLimit\Config\RedisRateLimitConfig;

return new RedisRateLimitConfig();

```

Custom storage engines can be integrated by implementing {b`Tempest\RateLimit\Config\RateLimitConfig`} and returning your own {b`Tempest\RateLimit\RateLimitStorage`} from `createStorage()`.

## Testing

{b`Tempest\RateLimit\Testing\RateLimitTester`} is accessible directly on `IntegrationTest` as `$this->rateLimit`. Calling `fake()` swaps the storage layer for an isolated in-memory driver, eliminating external infrastructure dependencies and test leakage:

```php
$this->rateLimit->fake();

$limit = RateLimit::perMinute(3)->withKey('login');

$this->rateLimit
    ->hit($limit, times: 2)
    ->assertHits($limit, 2)
    ->assertRemaining($limit, 1)
    ->assertNotThrottled($limit);

$this->rateLimit
    ->exhaust($limit)
    ->assertThrottled($limit);

```

Windows expire against the clock, so a mocked clock moved past the end of a window reopens it. Use `clear()` to discard the attempts recorded for a single limit between assertions, or call `fake()` again to discard all of them.

To allow every attempt, leaving throttled routes and manual `RateLimiter` calls unlimited, use:

```php
$this->rateLimit->preventThrottling();

```

Attempts are not recorded while throttling is prevented, so counters are left exactly as they were when `allowThrottling()` restores enforcement.

This state lasts for a single test. Call it from `setUp()` to cover an entire test case; `fake()` and `preventThrottling()` compose in either order.

HTTP tests interact with throttled routes naturally through simulated requests:

```php
$this->http->fromIp('203.0.113.9')->get('/api/posts')->assertOk();
$this->http->fromIp('203.0.113.9')->get('/api/posts')->assertStatus(Status::TOO_MANY_REQUESTS);

```

The counters behind `#[Throttle]` are keyed internally and are not addressable from a test, buckets included—a bucket is scoped to the client, so naming one would mean hand-assembling a key shape that carries no compatibility guarantee. To assert against a counter directly, give a limit a key of your own through a [rate limit profile](#limits-that-depend-on-the-request). That key is used as written, so it reaches the counter from anywhere:

```php
$limiter->clear(RateLimit::perMinute(5)->withKey('login:jon@doe.co'));
```
