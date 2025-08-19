<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tests\Tempest\Fixtures\Models\UserWithEager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

/**
 * @internal
 */
final class CircularEagerLoadingSimpleTest extends FrameworkIntegrationTestCase
{
    public function test_circular_eager_loading_does_not_cause_infinite_loop(): void
    {
        // This should not cause an infinite loop when resolving eager relations
        $userInspector = inspect(UserWithEager::class);

        // The resolveEagerRelations method should handle circular references
        $eagerRelations = $userInspector->resolveEagerRelations();
        $this->assertArrayHasKey('profile', $eagerRelations);

        // The profile.user relation should NOT be resolved because it would create a circular reference
        // The circular detection stops at the first occurrence of a repeated model type in the path
        $this->assertArrayNotHasKey('profile.user', $eagerRelations);

        // Test that only the profile relation is loaded
        $this->assertCount(1, $eagerRelations);
    }

    public function test_circular_with_relations_does_not_cause_infinite_loop(): void
    {
        $userInspector = inspect(UserWithEager::class);

        // This should handle circular references when using with() syntax
        $relations = $userInspector->resolveRelations('profile.user.profile');

        // We should get the profile and profile.user relations
        $this->assertArrayHasKey('profile', $relations);
        $this->assertArrayHasKey('user', $relations);

        // But not profile again after user (circular detection)
        $this->assertCount(2, $relations);
    }
}
