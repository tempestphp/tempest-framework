<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Container\Container;
use Tempest\Core\Kernel\FinishDeferredTasks;
use Tests\Tempest\Fixtures\Controllers\DeferController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\defer;
use function Tempest\uri;

/**
 * @internal
 */
final class DeferredTasksTest extends FrameworkIntegrationTestCase
{
    public function test_deferred_tasks_are_executed(): void
    {
        DeferController::$executed = false;

        $this->http
            ->get(uri(DeferController::class))
            ->assertOk();

        $this->container->invoke(FinishDeferredTasks::class);

        $this->assertTrue(DeferController::$executed);
    }

    public function test_deferred_tasks_are_executed_with_container_parameters(): void
    {
        $executed = false;

        defer(function (Container $container) use (&$executed): void {
            $container->invoke(function () use (&$executed): void {
                $executed = true;
            });
        });

        $this->container->invoke(FinishDeferredTasks::class);

        $this->assertTrue($executed);
    }
}
