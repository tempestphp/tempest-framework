<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Builder;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\ModelQueryBuilder;
use Tempest\Database\Tests\Builder\Fixtures\BarModel;
use Tempest\Database\Tests\Builder\Fixtures\FooModel;
use Tempest\Database\Tests\Builder\Fixtures\FooQueryBuilder;

/**
 * @internal
 */
final class ModelQueryBuilderTest extends TestCase
{
    public function test_default_query_builder(): void
    {
        $queryBuilder = BarModel::query();
        $this->assertInstanceOf(ModelQueryBuilder::class, $queryBuilder);
    }

    public function test_custom_query_builder(): void
    {
        $queryBuilder = FooModel::query();
        $this->assertInstanceOf(FooQueryBuilder::class, $queryBuilder);
    }
}
