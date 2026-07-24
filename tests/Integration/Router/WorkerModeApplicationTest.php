<?php

namespace Integration\Router;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Container\GenericContainer;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventBus;
use Tempest\Http\RequestFactory;
use Tempest\Router\ResponseSender;
use Tempest\Router\Router;
use Tempest\Router\WorkerModeApplication;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class WorkerModeApplicationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    #[BackupGlobals(true)]
    public function shutdown_and_reset_are_called(): void
    {
        unset($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
        $_COOKIE = [];

        $this->eventBus->preventEventHandling();

        $container = new GenericContainer();

        $container->singleton(EventBus::class, $this->container->get(EventBus::class));
        $container->singleton(Router::class, $this->container->get(Router::class));
        $container->singleton(RequestFactory::class, $this->container->get(RequestFactory::class));
        $container->singleton(ResponseSender::class, $this->container->get(ResponseSender::class));

        $container->singleton(Kernel::class, new FrameworkKernel(
            root: $this->kernel->root,
            discoveryLocations: $this->discoveryLocations,
            container: $container,
            longRunning: true,
        ));

        $application = new WorkerModeApplication($container);

        ob_start();
        $application->run();
        ob_get_clean();

        $this->eventBus->assertDispatched(KernelEvent::SHUTTING_DOWN);
        $this->eventBus->assertDispatched(KernelEvent::SHUTDOWN);
        $this->eventBus->assertDispatched(KernelEvent::RESETTING);
        $this->eventBus->assertDispatched(KernelEvent::RESET);
    }
}
