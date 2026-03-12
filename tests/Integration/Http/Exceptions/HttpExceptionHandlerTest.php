<?php

namespace Tests\Tempest\Integration\Http\Exceptions;

use Closure;
use Exception;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Container\Container;
use Tempest\Core\Exceptions\ExceptionsConfig;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tempest\Router\Exceptions\HttpExceptionHandler;
use Tempest\Router\ResponseSender;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\ExceptionThatConvertsToRedirectResponse;
use Tests\Tempest\Integration\Http\Fixtures\ExceptionWithContext;
use Tests\Tempest\Integration\Http\Fixtures\NullExceptionReporter;
use Throwable;

final class HttpExceptionHandlerTest extends FrameworkIntegrationTestCase
{
    public ?Response $response = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container->singleton(
            Kernel::class,
            fn () => new class($this->container->get(FrameworkKernel::class)) implements Kernel {
                public const string VERSION = '1.0.0-alpha.6';

                public string $root;

                public string $internalStorage;

                public array $discoveryLocations;

                public array $discoveryClasses;

                public Container $container;

                public function __construct(FrameworkKernel $kernel)
                {
                    $this->root = $kernel->root;
                    $this->internalStorage = $kernel->internalStorage;
                    $this->discoveryLocations = $kernel->discoveryLocations;
                    $this->discoveryClasses = $kernel->discoveryClasses;
                    $this->container = $kernel->container;
                }

                public static function boot(string $root, array $discoveryLocations = [], ?Container $container = null, ?string $internalStorage = null): self
                {
                    return Kernel::boot($root, $discoveryLocations, $container, $internalStorage); // @phpstan-ignore-line
                }

                public function shutdown(int|string $status = ''): never
                {
                    throw new Exception('Shutdown.');
                }
            },
        );

        $this->container->singleton(
            ResponseSender::class,
            fn () => new class($this) implements ResponseSender {
                public function __construct(
                    private HttpExceptionHandlerTest $case,
                ) {}

                public function send(Response $response): Response
                {
                    $this->case->response = $response;

                    return $response;
                }
            },
        );
    }

    public function test_exception_handler_shuts_down_kernel(): void
    {
        $this->expectExceptionMessage('Shutdown.');

        $handler = $this->container->get(HttpExceptionHandler::class);
        $handler->handle(new Exception());
    }

    public function test_exception_handler_sends_response_specified_by_sends_response(): void
    {
        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new ExceptionThatConvertsToRedirectResponse());
        });

        $this->assertInstanceOf(Redirect::class, $this->response);
        $this->assertContains('https://tempestphp.com', $this->response->getHeader('Location')->values);
    }

    public function test_exception_handler_returns_500_by_default(): void
    {
        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new Exception());
        });

        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $this->response->status);
        $this->assertStringContainsString('An unexpected server error occurred', $this->response->body->data['message']);
    }

    #[TestWith([Status::BAD_REQUEST])]
    #[TestWith([Status::INTERNAL_SERVER_ERROR])]
    #[TestWith([Status::NOT_FOUND])]
    #[TestWith([Status::FORBIDDEN])]
    #[TestWith([Status::METHOD_NOT_ALLOWED])]
    public function test_exception_handler_returns_same_code_as_http_exception(Status $status): void
    {
        $this->callExceptionHandler(function () use ($status): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new HttpRequestFailed($status));
        });

        $this->assertSame($status, $this->response->status);
    }

    public function test_exception_handler_runs_exception_processors(): void
    {
        $this->exceptions->preventProcessing(false);

        $this->container->get(ExceptionsConfig::class)->setReporters([NullExceptionReporter::class]);

        $thrown = new ExceptionWithContext();

        $this->callExceptionHandler(function () use ($thrown): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle($thrown);
        });

        $this->assertContains($thrown, NullExceptionReporter::$exceptions);
        $this->assertArrayHasKey('foo', NullExceptionReporter::$exceptions[0]->context());

        NullExceptionReporter::$exceptions = [];
    }

    private function callExceptionHandler(Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $throwable) {
            $this->assertSame('Shutdown.', $throwable->getMessage());
        }
    }
}
