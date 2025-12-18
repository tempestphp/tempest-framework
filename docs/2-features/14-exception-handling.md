---
title: Exception handling
description: "Learn how exception handling works, how to manually report exceptions, and how to customize exception rendering for HTTP responses."
---

## Overview

Tempest comes with its own exception handler, which provides a simple way to catch and process exceptions. During local development, Tempest uses [Whoops](https://github.com/filp/whoops) to display detailed error pages. In production, it displays a generic error page.

When an exception is thrown, it is caught and piped through the registered exception reporters. By default, the only registered exception reporter, {b`Tempest\Core\LoggingExceptionReporter`}, logs the exception.

Custom exception reporters can be created by implementing the {b`Tempest\Core\Exceptions\ExceptionReporter`} interface. Classes implementing this interface are automatically [discovered](../4-internals/02-discovery.md) and do not require manual registration.

## Processing exceptions

Exceptions can be reported without throwing them using the `process()` method of the {b`Tempest\Core\Exceptions\ExceptionProcessor`} interface. This allows putting exceptions through the reporting process without stopping the application's execution.

```php app/CreateUser.php
use Tempest\Core\Exceptions\ExceptionProcessor;

final readonly class CreateUser
{
    public function __construct(
        private ExceptionProcessor $exceptions
    ) {}

    public function __invoke(): void
    {
        try {
            // Some code that may throw an exception
        } catch (SomethingFailed $somethingFailed) {
            $this->exceptions->process($somethingFailed);
        }
    }
}
```

## Disabling exception logging

The default logging reporter, {b`Tempest\Core\Exceptions\LoggingExceptionReporter`}, is automatically added to the list of reporters. To disable it, create a {b`Tempest\Core\Exceptions\ExceptionsConfig`} [configuration file](../1-essentials/06-configuration.md#configuration-files) and set `logging` to `false`:

```php app/exceptions.config.php
use Tempest\Core\Exceptions\ExceptionsConfig;

return new ExceptionsConfig(
    logging: false,
);
```

## Adding context to exceptions

Exceptions can provide additional information for logging by implementing the {`Tempest\Core\ProvidesContext`} interface. The context data becomes available to exception processors.

```php
use Tempest\Core\ProvidesContext;

final readonly class UserWasNotFound extends Exception implements ProvidesContext
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

## Customizing exception rendering

Exception renderers provide control over how exceptions are rendered in HTTP responses. Custom renderers can be used to display specialized error pages for specific exception types, format errors differently based on content type (JSON, HTML, XML), or provide user-friendly error messages for common scenarios like 404 or validation failures.

To create a custom renderer, implement the {b`Tempest\Router\Exceptions\ExceptionRenderer`} interface. It requires a `canRender()` method to determine if the renderer can handle the given exception and request, and a `render()` method to produce the response:

```php app/NotFoundExceptionRenderer.php
use Tempest\Http\ContentType;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\Router\Exceptions\ExceptionRenderer;
use Throwable;

use function Tempest\view;

final class NotFoundExceptionRenderer implements ExceptionRenderer
{
    public function canRender(Throwable $throwable, Request $request): bool
    {
        if (! $request->accepts(ContentType::HTML)) {
            return false;
        }

        if (! $throwable instanceof HttpRequestFailed) {
            return false;
        }

        return $throwable->status === Status::NOT_FOUND;
    }

    public function render(Throwable $throwable): Response
    {
        return new NotFound(
            body: view('./404.view.php'),
        );
    }
}
```

:::info
Exception renderers are automatically [discovered](../4-internals/02-discovery.md) and checked in {b`#[Tempest\Core\Priority]`} order.
:::

## Testing

By extending {`Tempest\Framework\Testing\IntegrationTest`} from a test case, exception testing utilities may be accessed for making assertions about processed exceptions.

```php
// Allows exceptions to be processed during tests
$this->exceptions->allowProcessing();

// Assert that the exception was processed
$this->exceptions->assertProcessed(UserNotFound::class);

// Assert that the exception was not processed
$this->exceptions->assertNotProcessed(UserNotFound::class);

// Assert that no exceptions were processed
$this->exceptions->assertNothingProcessed();
```

By default, Tempest disables exception processing during tests. It is recommended to unit-test your own {b`Tempest\Core\Exceptions\ExceptionReporter`} implementations.
