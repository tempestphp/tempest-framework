<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use Tests\Tempest\Fixtures\Modules\Posts\PostController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class RoutesCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_command(): void
    {
        $this->console
            ->call('routes')
            ->assertContains('/create-post')
            ->assertContains(PostController::class);
    }
}
