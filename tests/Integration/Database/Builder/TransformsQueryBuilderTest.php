<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class TransformsQueryBuilderTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function select_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->select()
            ->transform(fn ($builder) => $builder->where('name', 'Frieren'));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Frieren'], $bindings);
    }

    #[Test]
    public function count_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->count()
            ->transform(fn ($builder) => $builder->where('name', 'Himmel'));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Himmel'], $bindings);
    }

    #[Test]
    public function update_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->update(name: 'Heiter')
            ->transform(fn ($builder) => $builder->where('id', 1));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Heiter', 1], $bindings);
    }

    #[Test]
    public function delete_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->delete()
            ->transform(fn ($builder) => $builder->where('name', 'Eisen'));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Eisen'], $bindings);
    }

    #[Test]
    public function insert_query_builder_transform(): void
    {
        $query = query(Author::class)
            ->insert(['name' => 'Stark'])
            ->transform(fn ($builder) => $builder->then(fn () => null));

        $bindings = $query->build()->bindings;

        $this->assertSame(['Stark'], $bindings);
    }

    #[Test]
    public function transform_returns_new_instance(): void
    {
        $original = query(Author::class)->select();

        $transformed = $original->transform(fn ($builder) => $builder);

        $this->assertNotSame($original, $transformed);
    }
}
