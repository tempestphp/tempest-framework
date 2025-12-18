<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Exceptions;

use Closure;
use Exception;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Container\Container;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Core\Priority;
use Tempest\Http\ContentType;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\HttpRequestFailed;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\Exceptions\ExceptionRenderer;
use Tempest\Router\Exceptions\HttpExceptionHandler;
use Tempest\Router\ResponseSender;
use Tempest\Router\RouteConfig;
use Tempest\Support\Json;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Throwable;

final class ExceptionRendererTest extends FrameworkIntegrationTestCase
{
    public ?Response $response = null;

    #[PreCondition]
    protected function configure(): void
    {
        $this->container->singleton(Kernel::class, fn () => new class($this->container->get(FrameworkKernel::class)) implements Kernel {
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
        });

        $this->container->singleton(ResponseSender::class, fn () => new class($this) implements ResponseSender {
            public function __construct(
                private ExceptionRendererTest $case,
            ) {}

            public function send(Response $response): Response
            {
                $this->case->response = $response;

                return $response;
            }
        });
    }

    #[Test]
    public function custom_json_exception_renderer(): void
    {
        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->addExceptionRenderer(CustomJsonValidationRenderer::class, Priority::HIGH);

        $this->container->singleton(
            Request::class,
            fn () => new GenericRequest(Method::GET, '/test', headers: ['Accept' => 'application/json']),
        );

        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new CustomValidationException('Custom validation failed'));
        });

        $body = Json\decode($this->response->body);

        $this->assertSame('json', $body['custom']);
        $this->assertSame('Custom validation failed', $body['message']);
        $this->assertContains('application/json', $this->response->getHeader('content-type')->values);
        $this->assertSame(Status::UNPROCESSABLE_CONTENT, $this->response->status);
    }

    #[Test]
    public function custom_html_exception_renderer(): void
    {
        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->addExceptionRenderer(CustomHtmlValidationRenderer::class, Priority::HIGH);

        $this->container->singleton(
            Request::class,
            fn () => new GenericRequest(Method::GET, '/test', headers: ['Accept' => 'text/html']),
        );

        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new CustomValidationException('Custom validation failed'));
        });

        $this->assertSame(Status::UNPROCESSABLE_CONTENT, $this->response->status);
        $this->assertContains('text/html', $this->response->getHeader('content-type')->values);
        $this->assertStringContainsString('Custom validation failed', $this->response->body);
    }

    #[Test]
    public function falls_back_to_default_renderer_when_no_custom_match(): void
    {
        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->addExceptionRenderer(CustomJsonValidationRenderer::class, Priority::HIGH);

        $this->container->singleton(
            Request::class,
            fn () => new GenericRequest(Method::GET, '/test', headers: ['Accept' => 'application/json']),
        );

        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new Exception('Regular exception'));
        });

        $this->assertNotNull($this->response, 'Response should not be null');
        $this->assertSame(Status::INTERNAL_SERVER_ERROR, $this->response->status);
        $this->assertSame('Internal Server Error', $this->response->body['message']);
    }

    #[Test]
    public function priority_ordering(): void
    {
        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->addExceptionRenderer(CustomJsonValidationRenderer::class, Priority::LOW);
        $routeConfig->addExceptionRenderer(HighPriorityRenderer::class, Priority::HIGHEST);

        $this->container->singleton(
            Request::class,
            fn () => new GenericRequest(Method::GET, '/test', headers: ['Accept' => 'application/json']),
        );

        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new CustomValidationException('Test'));
        });

        $body = Json\decode($this->response->body);

        $this->assertSame('high', $body['priority']);
        $this->assertNotNull($this->response, 'Response should not be null');
    }

    #[Test]
    public function custom_404_renderer(): void
    {
        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->addExceptionRenderer(Custom404Renderer::class, Priority::HIGH);

        $this->container->singleton(
            Request::class,
            fn () => new GenericRequest(Method::GET, '/test', headers: ['Accept' => 'text/html']),
        );

        $this->callExceptionHandler(function (): void {
            $handler = $this->container->get(HttpExceptionHandler::class);
            $handler->handle(new HttpRequestFailed(status: Status::NOT_FOUND));
        });

        $this->assertSame(Status::NOT_FOUND, $this->response->status);
        $this->assertStringContainsString('Custom 404 page', (string) $this->response->body);
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

final class CustomValidationException extends Exception
{
}

#[Priority(Priority::HIGH)]
final readonly class CustomJsonValidationRenderer implements ExceptionRenderer
{
    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $throwable instanceof CustomValidationException && $request->accepts(ContentType::JSON);
    }

    public function render(Throwable $throwable): Response
    {
        return new GenericResponse(
            status: Status::UNPROCESSABLE_CONTENT,
            body: Json\encode(['custom' => 'json', 'message' => $throwable->getMessage()]),
            headers: [
                'Content-Type' => ContentType::JSON->value,
            ],
        );
    }
}

#[Priority(Priority::HIGH)]
final readonly class CustomHtmlValidationRenderer implements ExceptionRenderer
{
    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $throwable instanceof CustomValidationException && $request->accepts(ContentType::HTML);
    }

    public function render(Throwable $throwable): Response
    {
        return new GenericResponse(
            status: Status::UNPROCESSABLE_CONTENT,
            body: '<html><body>' . $throwable->getMessage() . '</body></html>',
            headers: [
                'Content-Type' => ContentType::HTML->value,
            ],
        );
    }
}

final readonly class HighPriorityRenderer implements ExceptionRenderer
{
    public function canRender(Throwable $throwable, Request $request): bool
    {
        return $throwable instanceof CustomValidationException && $request->accepts(ContentType::JSON);
    }

    public function render(Throwable $throwable): Response
    {
        return new GenericResponse(
            status: Status::UNPROCESSABLE_CONTENT,
            body: Json\encode(['priority' => 'high']),
            headers: [
                'Content-Type' => ContentType::JSON->value,
            ],
        );
    }
}

final readonly class Custom404Renderer implements ExceptionRenderer
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
        return new GenericResponse(
            status: Status::NOT_FOUND,
            body: '<html><body><h1>Custom 404 page</h1><p>The page you are looking for does not exist.</p></body></html>',
            headers: [
                'Content-Type' => ContentType::HTML->value,
            ],
        );
    }
}
