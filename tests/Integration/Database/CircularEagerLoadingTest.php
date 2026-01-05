<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateProfileWithEagerTable;
use Tests\Tempest\Fixtures\Migrations\CreateUserWithEagerTable;
use Tests\Tempest\Fixtures\Models\ProfileWithEager;
use Tests\Tempest\Fixtures\Models\UserWithEager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;
use function Tempest\Database\query;

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

    public function test_it_saves_and_loads_relations_without_causing_infinite_loop(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateUserWithEagerTable::class,
            CreateProfileWithEagerTable::class,
        );

        $user = query(UserWithEager::class)->create(
            name: 'John Doe',
        );

        $profile = query(ProfileWithEager::class)->create(
            bio: 'Test',
            user_id: $user->id->value,
        );

        $profile = query(ProfileWithEager::class)->findById($profile->id);

        $user = query(UserWithEager::class)->findById($user->id);

        $this->assertTrue($profile->id->equals($user->profile->id));
        $this->assertTrue($user->id->equals($profile->user->id));
    }
}
