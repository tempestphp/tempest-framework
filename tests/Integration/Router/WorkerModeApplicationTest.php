<?php

namespace Integration\Router;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\KernelEvent;
use Tempest\Router\WorkerModeApplication;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class WorkerModeApplicationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function test_shutdown_and_reset_are_called(): void
    {
        $this->http->get('/');
        $this->eventBus->preventEventHandling();

        $application = new WorkerModeApplication($this->container);

        ob_start();
        $application->run();
        ob_get_clean();

        $this->eventBus->assertDispatched(KernelEvent::SHUTTING_DOWN);
        $this->eventBus->assertDispatched(KernelEvent::SHUTDOWN);
        $this->eventBus->assertDispatched(KernelEvent::RESETTING);
        $this->eventBus->assertDispatched(KernelEvent::RESET);
    }
}
