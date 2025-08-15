<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class TransformsQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_select_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->select()
            ->transform(fn ($builder) => $builder->where('name', 'Frieren'));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Frieren'], $bindings);
    }

    public function test_count_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->count()
            ->transform(fn ($builder) => $builder->where('name', 'Himmel'));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Himmel'], $bindings);
    }

    public function test_update_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->update(name: 'Heiter')
            ->transform(fn ($builder) => $builder->where('id', 1));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Heiter', 1], $bindings);
    }

    public function test_delete_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->delete()
            ->transform(fn ($builder) => $builder->where('name', 'Eisen'));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Eisen'], $bindings);
    }

    public function test_insert_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->insert(['name' => 'Stark'])
            ->transform(fn ($builder) => $builder->then(fn () => null));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Stark'], $bindings);
    }

    public function test_transform_returns_new_instance(): void
    {
        $original = query(Author::class)->select();

        $transformed = $original->transform(fn ($builder) => $builder);

        $this->assertNotSame($original, $transformed);
    }
}
