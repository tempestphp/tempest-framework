<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\Kernel\FinishDeferredTasks;
use function Tempest\uri;
use Tests\Tempest\Fixtures\Controllers\DeferController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

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
}
