---
title: Exception handling
description: "Learn how to gracefully handle exceptions in your application by writing exception processors."
---

## Overview

Tempest comes with its own exception handler, which provides a simple way to catch and process exceptions. During local development, Tempest uses [Whoops](https://github.com/filp/whoops) to display detailed error pages. In production, it will show a generic error page.

When an exception is thrown, it will be caught and piped through the registered exception processors. By default, the only registered exception processor, {b`Tempest\Core\LogExceptionProcessor`}, will simply log the exception.

Of course, you may create your own exception processors. This is done by creating a class that implements the {`Tempest\Core\ExceptionProcessor`} interface. Classes implementing this interface are automatically [discovered](../1-essentials/05-discovery.md), so you don't need to register them manually.

## Reporting exceptions

Sometimes, you may want to report an exception without necessarily throwing it. For example, you may want to log an exception, but not stop the execution of the application. To do this, you can use the `Tempest\report()` function.

```php
use function Tempest\report;

try {
    // Some code that may throw an exception
} catch (SomethingFailed $e) {
    report($e);
}
```

## Disabling default logging

Exception processors are discovered when Tempest boots, then stored in the `exceptionProcessors` property of {`Tempest\Core\AppConfig`}. The default logging processor, {b`Tempest\Core\LogExceptionProcessor`}, is automatically added to the list of processors.

To disable exception logging, you may remove it in a `KernelEvent::BOOTED` event handler:

```php
use Tempest\Core\AppConfig;
use Tempest\Core\KernelEvent;
use Tempest\Core\LogExceptionProcessor;
use Tempest\EventBus\EventHandler;
use Tempest\Support\Arr;

final readonly class DisableExceptionLogging
{
    public function __construct(
        private AppConfig $appConfig,
    ) {
    }

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(): void
    {
        Arr\forget_values($this->appConfig->exceptionProcessors, LogExceptionProcessor::class);
    }
}
```

## Adding context to exceptions

Sometimes, an exception may have information that you would like to be logged. By implementing the {`Tempest\Core\HasContext`} interface on an exception class, you can provide additional context that will be logged—and available to other processors.

```php
use Tempest\Core\HasContext;

final readonly class UserWasNotFound extends Exception implements HasContext
{
    public function __construct(private string $userId)
    {
        parent::__construct("User {$userId} not found.");
    }

    public function context(): array
    {
        return [
            'user_id' => $this->userId,
        ];
    }
}
```

## Customizing the error page

In production, when an uncaught exception occurs, Tempest displays a minimalistic, generic error page. You may customize this behavior by adding a middleware dedicated to catching {b`Tempest\Http\HttpRequestFailed`} exceptions.

For instance, you may display a branded error page by providing a view:

```php
use Tempest\Http\HttpRequestFailed;
use Tempest\Router\HttpMiddleware;
use function Tempest\view;

final class CatchHttpRequestFailuresMiddleware implements HttpMiddleware
{
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        try {
            return $next($request);
        } catch (HttpRequestFailed $failure) {
            return new GenericResponse(
                status: $failure->status,
                body: view('./error.view.php', failure: $failure),
            );
        }
    }
}
```

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the exception testing utilities, which allow you to make assertions about reported exceptions.

```php
// Prevents exceptions from being actually processed
$this->exceptions->preventReporting();

// Asserts that the exception was reported
$this->exceptions->assertReported(UserNotFound::class);

// Asserts that the exception was not reported
$this->exceptions->assertNotReported(UserNotFound::class);

// Asserts that no exceptions were reported
$this->exceptions->assertNothingReported();
```
