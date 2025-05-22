<?php

namespace Tests\Tempest\Integration\Http;

use Closure;
use Exception;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Http\HttpException;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tempest\Router\Exceptions\HttpExceptionHandler;
use Tempest\Router\Exceptions\NotFoundException;
use Tempest\Router\ResponseSender;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\ExceptionThatConvertsToRedirectResponse;
use Tests\Tempest\Integration\Http\Fixtures\ExceptionWithContext;
use Tests\Tempest\Integration\Http\Fixtures\NullExceptionProcessor;

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

                public static function boot(string $root, array $discoveryLocations = [], ?Container $container = null): self
                {
                    // This is just to make static analysis pass, this is never called.
                    // @mago-expect analysis/undefined-function-or-method
                    // @phpstan-ignore-next-line
                    return Kernel::boot($root, $discoveryLocations, $container);
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
        $this->assertStringContainsString('An unexpected server error occurred', $this->render($this->response->body));
    }

    public function test_exception_handler_returns_404_for_router_not_found_execption(): void
    {
        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new NotFoundException());
        });

        $this->assertSame(Status::NOT_FOUND, $this->response->status);
        $this->assertStringContainsString('This page could not be found on the server', $this->render($this->response->body));
    }

    #[TestWith([Status::BAD_REQUEST])]
    #[TestWith([Status::INTERNAL_SERVER_ERROR])]
    #[TestWith([Status::NOT_FOUND])]
    #[TestWith([Status::FORBIDDEN])]
    #[TestWith([Status::METHOD_NOT_ALLOWED])]
    public function test_exception_handler_returns_sane_code_as_http_exception(Status $status): void
    {
        $this->callExceptionHandler(function () use ($status): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new HttpException($status));
        });

        $this->assertSame($status, $this->response->status);
    }

    public function test_exception_handler_runs_exception_processors(): void
    {
        $this->exceptions->preventReporting(false);

        $this->container->get(AppConfig::class)->exceptionProcessors[] = NullExceptionProcessor::class;

        $thrown = new ExceptionWithContext();

        $this->callExceptionHandler(function () use ($thrown): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle($thrown);
        });

        $this->assertContains($thrown, NullExceptionProcessor::$exceptions);
        $this->assertArrayHasKey('foo', NullExceptionProcessor::$exceptions[0]->context());

        NullExceptionProcessor::$exceptions = [];
    }

    private function callExceptionHandler(Closure $callback): void
    {
        try {
            $callback();
        } catch (\Throwable $throwable) {
            $this->assertSame('Shutdown.', $throwable->getMessage());
        }
    }
}
