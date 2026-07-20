<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Framework\Commands;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Fixtures\Modules\Posts\PostController;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RoutesCommandTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function displays_uri_and_controller(): void
    {
        $this->console
            ->call('routes')
            ->assertContains('/create-post')
            ->assertContains(PostController::class);
    }

    #[Test]
    public function outputs_as_json(): void
    {
        $this->console
            ->call('routes', ['--json'])
            ->assertJson();
    }
}
