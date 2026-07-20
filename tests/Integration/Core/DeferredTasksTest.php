<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Container\Container;
use Tempest\Core\DeferredTasks;
use Tempest\Core\Kernel\FinishDeferredTasks;
use Tests\Tempest\Fixtures\Controllers\DeferController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\defer;
use function Tempest\Router\uri;

/**
 * @internal
 */
final class DeferredTasksTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function deferred_tasks_are_executed(): void
    {
        DeferController::$executed = false;

        $this->http
            ->get(uri(DeferController::class))
            ->assertOk();

        $this->container->invoke(FinishDeferredTasks::class);

        $this->assertTrue(DeferController::$executed);
        $this->assertEmpty($this->container->get(DeferredTasks::class)->getTasks());
    }

    #[Test]
    public function deferred_tasks_are_executed_with_container_parameters(): void
    {
        $executed = false;

        defer(function (Container $container) use (&$executed): void {
            $container->invoke(function () use (&$executed): void {
                $executed = true;
            });
        });

        $this->container->invoke(FinishDeferredTasks::class);

        $this->assertTrue($executed);
        $this->assertEmpty($this->container->get(DeferredTasks::class)->getTasks());
    }

    #[Test]
    public function tasks_are_reset(): void
    {
        $first = $this->container->get(DeferredTasks::class);

        $this->container->reset();

        $second = $this->container->get(DeferredTasks::class);

        $this->assertNotSame($first, $second);
    }
}
