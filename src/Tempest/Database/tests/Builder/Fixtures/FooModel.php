<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Builder\Fixtures;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\QueryBuilder;

#[QueryBuilder(FooQueryBuilder::class)]
final class FooModel implements DatabaseModel
{
    use IsDatabaseModel;
}
