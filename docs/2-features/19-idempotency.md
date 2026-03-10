---
title: Idempotency
description: "Prevent duplicate side effects for HTTP routes and command bus commands by storing and replaying the result of the first execution."
---

## Overview

Payment processing, order creation, resource provisioning - any operation where retrying the same request should not produce duplicate side effects. Timeouts, client retries, and accidental double clicks all cause the same problem: the server cannot distinguish a retry from a new request.

The `tempest/idempotency` package solves this by storing the result of the first execution and replaying it for subsequent requests with the same idempotency key. It supports both [HTTP routes](#idempotent-routes) and [command bus commands](#idempotent-commands).

## Idempotent routes

Add the {b`Tempest\Idempotency\Attributes\Idempotent`} attribute to a controller method. Clients send an `{txt}{:hlvalueproperty:Idempotency-Key:}` header with a unique value (typically a UUID). The first request executes normally and caches the response. Subsequent requests with the same key replay the cached response without re-executing the handler.

```php app/OrderController.php
use Tempest\Router\Post;
use Tempest\Http\Response;
use Tempest\Http\GenericResponse;
use Tempest\Http\Status;
use Tempest\Idempotency\Attributes\Idempotent;

final readonly class OrderController
{
    #[Post('/orders')]
    #[Idempotent]
    public function create(CreateOrderRequest $request): Response
    {
        $order = $this->orderService->create($request);

        return new GenericResponse(
            status: Status::CREATED,
            body: ['id' => $order->id],
        );
    }
}
```

The client must include the idempotency key as a header:

```
POST /orders HTTP/1.1
Idempotency-Key: 550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json

{"product": "widget", "quantity": 3}
```

When a cached response is replayed, the response includes an `{:hl-property:idempotency-replayed:}: true` header so the client can distinguish replays from original executions.

### Supported methods

Idempotency is only supported for `POST` and `PATCH` routes. Applying `#[Idempotent]` to a `GET`, `PUT`, `DELETE`, or other method will throw an {b`Tempest\Idempotency\Exceptions\IdempotencyMethodWasNotSupported`} exception. `GET` is inherently idempotent, `PUT` and `DELETE` are idempotent by definition in HTTP semantics, and only `POST` and `PATCH` produce non-idempotent side effects.

### Scope resolver

Idempotency keys must be scoped per user or client to prevent key collisions across different actors. This is done by implementing the {b`Tempest\Idempotency\IdempotencyScopeResolver`} interface and registering it in the container.

The `resolve()` method receives the current request and must return a string that uniquely identifies the caller - such as a user ID, session ID, or API key:

```php app/UserIdempotencyScopeResolver.php
use Tempest\Http\Request;
use Tempest\Idempotency\IdempotencyScopeResolver;

final readonly class UserIdempotencyScopeResolver implements IdempotencyScopeResolver
{
    public function __construct(
        private AuthManager $auth,
    ) {}

    public function resolve(Request $request): string
    {
        return (string) $this->auth->currentUser()->id;
    }
}
```

:::warning
A scope resolver is required. If no implementation of {b`Tempest\Idempotency\IdempotencyScopeResolver`} is registered in the container, the middleware will fail at construction time.
:::

### Per-route overrides

The `#[Idempotent]` attribute accepts optional TTL parameters to override the global configuration on a per-route basis. For route-specific settings like key requirement and header name, use the {b`Tempest\Idempotency\Attributes\IdempotentRoute`} attribute alongside `#[Idempotent]`:

```php app/PaymentController.php
use Tempest\Router\Post;
use Tempest\Http\Response;
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Attributes\IdempotentRoute;

final readonly class PaymentController
{
    #[Post('/payments')]
    #[Idempotent(ttlInSeconds: 172_800)]
    #[IdempotentRoute(requireKey: true)]
    public function charge(ChargeRequest $request): Response
    {
        // Cached response persists for 48 hours instead of the default 24
    }
}
```

#### `#[Idempotent]` parameters

| Parameter                             | Type               | Description                                                                                              |
|---------------------------------------|--------------------|----------------------------------------------------------------------------------------------------------|
| `{:hl-property:ttlInSeconds:}`        | `{:hl-type:?int:}` | How long a completed response is cached. Defaults to the config value (86400 / 24 hours).                |
| `{:hl-property:pendingTtlInSeconds:}` | `{:hl-type:?int:}` | How long a pending (in-progress) record is considered active. Defaults to the config value (60 seconds). |

#### `#[IdempotentRoute]` parameters

| Parameter                    | Type                  | Description                                                                                                     |
|------------------------------|-----------------------|-----------------------------------------------------------------------------------------------------------------|
| `{:hl-property:requireKey:}` | `{:hl-type:?bool:}`   | Whether requests without the idempotency key header should be rejected with a 400 response. Defaults to `true`. |
| `{:hl-property:header:}`     | `{:hl-type:?string:}` | The header name to read the idempotency key from. Defaults to `{txt}{:hl-value:Idempotency-Key:}`.              |

When `{:hl-property:requireKey:}` is set to `false`, requests without the header bypass idempotency protection entirely and execute normally.

### Class-level application

The `#[Idempotent]` attribute can be applied at the class level to make all routes in a controller idempotent:

```php app/ApiOrderController.php
use Tempest\Router\Post;
use Tempest\Router\Patch;
use Tempest\Http\Response;
use Tempest\Idempotency\Attributes\Idempotent;

#[Idempotent]
final readonly class ApiOrderController
{
    #[Post('/api/orders')]
    public function create(CreateOrderRequest $request): Response { /* … */ }

    #[Patch('/api/orders/{id}')]
    public function update(int $id, UpdateOrderRequest $request): Response { /* … */ }
}
```

### Response behavior

The middleware produces different responses depending on the state of the idempotency key:

| Scenario                            | Status          | Description                                                                                                            |
|-------------------------------------|-----------------|------------------------------------------------------------------------------------------------------------------------|
| No existing record                  | -               | The request executes normally and the response is cached.                                                              |
| Completed record, same payload      | Original status | The cached response is replayed with an `{:hl-property:idempotency-replayed:}: true` header.                           |
| Completed record, different payload | `422`           | The key was already used with a different request body.                                                                |
| Pending record (in progress)        | `409`           | Another request with the same key is currently being processed. A `{:hl-property:retry-after:}: 1` header is included. |
| Missing key (when required)         | `400`           | The `{txt}{:hl-value:Idempotency-Key:}` header was not provided.                                                       |

### How it works

The `#[Idempotent]` attribute is a [route decorator](../1-essentials/01-routing.md#route-decorators) that adds {b`Tempest\Idempotency\Middleware\IdempotencyMiddleware`} to the route's middleware stack. The middleware:

1. Reads the idempotency key from the request header.
2. Computes a fingerprint of the request (method, URI, body, and query parameters).
3. Acquires a cache lock to prevent concurrent processing of the same key.
4. Checks for an existing record in the idempotency store.
5. If no record exists, saves a pending record, executes the handler, and stores the completed response.
6. If the handler throws an exception, the pending record is deleted so the request can be retried.

A heartbeat mechanism keeps pending records alive during long-running requests, preventing other processes from incorrectly taking over an operation that is still in progress.

## Idempotent commands

Add the {b`Tempest\Idempotency\Attributes\Idempotent`} attribute to prevent duplicate dispatches. When the same command is dispatched more than once, the duplicate is silently skipped. The attribute can be placed on the command class or on the handler method.

On the command class:

```php app/ImportInvoicesCommand.php
use Tempest\Idempotency\Attributes\Idempotent;

#[Idempotent]
final readonly class ImportInvoicesCommand
{
    public function __construct(
        public string $vendorId,
        public string $month,
    ) {}
}
```

Or on the handler method:

```php app/ImportInvoicesHandler.php
use Tempest\CommandBus\CommandHandler;
use Tempest\Idempotency\Attributes\Idempotent;

final class ImportInvoicesHandler
{
    #[Idempotent]
    #[CommandHandler]
    public function handleImportInvoices(ImportInvoicesCommand $command): void
    {
        // Only executes once per unique command payload.
        // Duplicate dispatches are silently skipped.
    }
}
```

When placed on both the command class and the handler, the command class takes precedence.

By default, the idempotency key is derived from a fingerprint of the command's properties. Two commands with identical property values produce the same fingerprint and are considered duplicates.

### Explicit idempotency keys

Commands can provide an explicit key by implementing the {b`Tempest\Idempotency\HasIdempotencyKey`} interface. This is useful when the deduplication key should be a specific business identifier rather than the full payload:

```php app/ProcessPaymentCommand.php
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\HasIdempotencyKey;

#[Idempotent]
final readonly class ProcessPaymentCommand implements HasIdempotencyKey
{
    public function __construct(
        public string $paymentId,
        public int $amount,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->paymentId;
    }
}
```

When using explicit keys, the fingerprint of the command payload is still verified. If the same key is dispatched with a different payload, an {b`Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed`} exception is thrown.

### Per-command TTL overrides

The `#[Idempotent]` attribute accepts the same optional TTL parameters for commands as it does for routes:

```php
#[Idempotent(ttlInSeconds: 3600, pendingTtlInSeconds: 30)]
final readonly class ProcessPaymentCommand { /* … */ }
```

| Parameter                             | Type               | Description                                                                                |
|---------------------------------------|--------------------|--------------------------------------------------------------------------------------------|
| `{:hl-property:ttlInSeconds:}`        | `{:hl-type:?int:}` | How long the completed record is cached. Defaults to the config value (86400 / 24 hours).  |
| `{:hl-property:pendingTtlInSeconds:}` | `{:hl-type:?int:}` | How long a pending record is considered active. Defaults to the config value (60 seconds). |

## Configuration

The idempotency package is configured by creating an `idempotency.config.php` file. All settings have sensible defaults:

```php app/idempotency.config.php
use Tempest\Idempotency\Config\IdempotencyConfig;

return new IdempotencyConfig(
    header: 'Idempotency-Key',
    requireKey: true,
    ttlInSeconds: 86_400,
    pendingTtlInSeconds: 60,
    cachePrefix: 'idempotency',
);
```

| Parameter                             | Default                             | Description                                                                 |
|---------------------------------------|-------------------------------------|-----------------------------------------------------------------------------|
| `{:hl-property:header:}`              | `{txt}{:hl-value:Idempotency-Key:}` | The HTTP header name to read the idempotency key from.                      |
| `{:hl-property:requireKey:}`          | `{:hl-type:true:}`                  | Whether to reject requests that do not include the idempotency key header.  |
| `{:hl-property:ttlInSeconds:}`        | `86400` (24h)                       | How long a completed response is cached.                                    |
| `{:hl-property:pendingTtlInSeconds:}` | `60`                                | How long a pending record is considered active before it can be taken over. |
| `{:hl-property:cachePrefix:}`         | `{:hl-value:idempotency:}`          | Prefix for cache keys in the idempotency store.                             |
| `{:hl-property:storeClass:}`          | `{:hl-type:CacheIdempotencyStore:}` | The {b`Tempest\Idempotency\Store\IdempotencyStore`} implementation to use.  |

### Custom stores

The default store uses Tempest's [cache](./06-cache.md) component. A custom store can be created by implementing the {b`Tempest\Idempotency\Store\IdempotencyStore`} interface and setting the `storeClass` in the configuration:

```php app/idempotency.config.php
use Tempest\Idempotency\Config\IdempotencyConfig;
use App\RedisIdempotencyStore;

return new IdempotencyConfig(
    storeClass: RedisIdempotencyStore::class,
);
```

## Limitations
- **Windows is not supported.** The heartbeat mechanism relies on `pcntl_alarm()` and
  `pcntl_signal()`, which are not available on Windows. Attempting to use idempotency on Windows will throw an {b`Tempest\Idempotency\Exceptions\IdempotencyPlatformWasNotSupported`} exception.
- **Stored responses must be serializable.** Response bodies are stored using PHP serialization or JSON encoding. Non-serializable bodies (such as generators or views) are stored as type name strings and will not reproduce the original output on replay.
