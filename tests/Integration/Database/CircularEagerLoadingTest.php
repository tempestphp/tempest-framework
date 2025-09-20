<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tests\Tempest\Fixtures\Models\UserWithEager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

/**
 * @internal
 */
final class CircularEagerLoadingTest extends FrameworkIntegrationTestCase
{
    public function test_circular_eager_loading_does_not_cause_infinite_loop(): void
    {
        $userInspector = inspect(UserWithEager::class);
        $eagerRelations = $userInspector->resolveEagerRelations();

        $this->assertArrayHasKey('profile', $eagerRelations);
        $this->assertArrayNotHasKey('profile.user', $eagerRelations);
        $this->assertCount(1, $eagerRelations);
    }

    public function test_circular_with_relations_does_not_cause_infinite_loop(): void
    {
        $userInspector = inspect(UserWithEager::class);
        $relations = $userInspector->resolveRelations('profile.user.profile');

        $this->assertArrayHasKey('profile', $relations);
        $this->assertArrayHasKey('user', $relations);
        $this->assertCount(2, $relations);
    }
}
