<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Commands;

use App\Modules\Posts\PostController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class RoutesCommandTest extends FrameworkIntegrationTestCase
{
    public function test_migrate_command()
    {
        $this->console
            ->call('routes')
            ->assertContains('/create-post')
            ->assertContains(PostController::class);
    }
}
