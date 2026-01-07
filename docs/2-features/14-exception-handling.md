---
title: Exception handling
description: "Learn how exception handling works, how to manually report exceptions, and how to customize exception rendering for HTTP responses."
---

## Overview

Tempest comes with an exception handler that provides a simple way to report exceptions and render error responses.

Custom [exception reporters](#writing-exception-reporters) can be created by implementing the {b`Tempest\Core\Exceptions\ExceptionReporter`} interface, and custom [exception renderers](#customizing-exception-rendering) can be created by implementing {b`Tempest\Router\Exceptions\ExceptionRenderer`}. These classes are automatically [discovered](../1-essentials/05-discovery.md) and do not require manual registration.

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

## Writing exception reporters

Exception reporters allow defining custom reporting logic for exceptions, such as sending them to external error tracking services like Sentry or logging them to specific destinations.

To create a custom reporter, implement the {b`Tempest\Core\Exceptions\ExceptionReporter`} interface and define a `report()` method:

```php app/SentryExceptionReporter.php
use Tempest\Core\Exceptions\ExceptionReporter;
use Throwable;

final class SentryExceptionReporter implements ExceptionReporter
{
    public function __construct(
        private SentryClient $sentry,
    ) {}

    public function report(Throwable $throwable): void
    {
        $this->sentry->captureException($throwable);
    }
}
```

Exception reporters are automatically [discovered](../4-internals/02-discovery.md) and registered. All registered reporters are invoked whenever an exception is processed, allowing multiple reporters to handle the same exception.

For example, the default logging reporter logs to a file, while the reporter above sends the error to Sentry.

If an exception reporter throws an exception during execution, it is silently caught to prevent infinite loops. This ensures that a failing reporter doesn't prevent other reporters from running.

### Accessing exception context

Exceptions can implement the {b`Tempest\Core\ProvidesContext`} interface, which reporters can leverage to provide additional context data during reporting:

```php app/SentryExceptionReporter.php
use Tempest\Core\Exceptions\ExceptionReporter;
use Tempest\Core\ProvidesContext;
use Sentry\State\HubInterface as Sentry;
use Sentry\State\Scope;

final class SentryExceptionReporter implements ExceptionReporter
{
    public function __construct(
        private readonly Sentry $sentry,
    ) {}

    public function report(Throwable $throwable): void
    {
        $this->sentry->withScope(function (Scope $scope) use ($throwable) {
            if ($throwable instanceof ProvidesContext) {
                $scope->withContext($throwable->context());
            }

            $scope->captureException($throwable);
        });
    }
}
```

### Conditional reporting

Reporters can implement conditional logic to only report specific exception types or under certain conditions. There is no built-in filtering mechanism; reporters are responsible for determining when to report an exception.

```php app/CriticalErrorReporter.php
use Tempest\Core\Exceptions\ExceptionReporter;
use Throwable;

final class CriticalErrorReporter implements ExceptionReporter
{
    public function __construct(
        private AlertService $alerts,
    ) {}

    public function report(Throwable $throwable): void
    {
        if (! $throwable instanceof CriticalException) {
            return;
        }

        $this->alerts->sendCriticalAlert(
            message: $throwable->getMessage(),
        );
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

use function Tempest\View\view;

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
