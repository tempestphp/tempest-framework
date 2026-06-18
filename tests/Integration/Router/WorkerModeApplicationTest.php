<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Router;

use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\Http\GenericResponse;
use Tempest\Http\Input\InputStream;
use Tempest\Http\RequestFactory;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\ResponseSender;
use Tempest\Router\Router;
use Tempest\Router\WorkerModeApplication;
use Tests\Tempest\Integration\IntegrationTestCase;

final class WorkerModeApplicationTest extends IntegrationTestCase
{
    #[Test]
    public function test_it_dispatches_the_current_request_and_sends_the_response(): void
    {
        $response = new GenericResponse(Status::OK, 'Hello worker');

        $application = $this->createApplication(
            response: $response,
            assertDispatchedRequest: function (ServerRequestInterface $request): void {
                $this->assertSame(['name' => 'Tempest'], $request->getParsedBody());
            },
            assertSentResponse: function (Response $sentResponse) use ($response): void {
                $this->assertSame($response, $sentResponse);
            },
        );

        $application->run();
    }

    #[Test]
    public function test_it_shuts_down_the_kernel(): void
    {
        $kernel = $this->createMock(Kernel::class);
        $kernel->expects($this->once())->method('shutdown');

        $application = $this->createApplication(kernel: $kernel);

        $application->run();
    }

    #[Test]
    public function test_it_resets_the_container(): void
    {
        $container = $this->createMock(Container::class);
        $container->expects($this->once())->method('reset')->willReturnSelf();

        $application = $this->createApplication(container: $container);

        $application->run();
    }

    private function createApplication(
        ?Container $container = null,
        ?Kernel $kernel = null,
        ?Response $response = null,
        ?callable $assertDispatchedRequest = null,
        ?callable $assertSentResponse = null,
    ): WorkerModeApplication {
        if (! $container instanceof Container) {
            $container = $this->createMock(Container::class);
            $container->expects($this->once())->method('reset')->willReturnSelf();
        }

        if (! $kernel instanceof Kernel) {
            $kernel = $this->createMock(Kernel::class);
            $kernel->expects($this->once())->method('shutdown');
        }

        $response ??= new GenericResponse(Status::OK);

        $inputStream = $this->createMock(InputStream::class);
        $inputStream
            ->expects($this->once())
            ->method('parse')
            ->willReturn(['name' => 'Tempest']);

        $requestFactory = new RequestFactory($inputStream);

        $router = $this->createMock(Router::class);
        $router
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (ServerRequestInterface $request) use ($response, $assertDispatchedRequest): Response {
                $assertDispatchedRequest?->__invoke($request);

                return $response;
            });

        $responseSender = $this->createMock(ResponseSender::class);
        $responseSender
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function (Response $response) use ($assertSentResponse): Response {
                $assertSentResponse?->__invoke($response);

                return $response;
            });

        $container
            ->expects($this->exactly(4))
            ->method('get')
            ->willReturnCallback(fn (string $className): object => match ($className) {
                Router::class => $router,
                RequestFactory::class => $requestFactory,
                ResponseSender::class => $responseSender,
                Kernel::class => $kernel,
            });

        return new WorkerModeApplication($container);
    }
}
