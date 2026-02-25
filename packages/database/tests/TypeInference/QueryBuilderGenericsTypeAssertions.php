<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\TypeInference;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Tests\QueryStatements\StubModel;

use function Tempest\Database\query;

abstract class ParentStubModel
{
    use IsDatabaseModel;

    public PrimaryKey $id;
}

final class ChildStubModel extends ParentStubModel
{
}

$queryBuilder = query(StubModel::class);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\QueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $queryBuilder);

$selectQueryBuilder = $queryBuilder->select();
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $selectQueryBuilder);

$whereField = $selectQueryBuilder->whereField('id', 1);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $whereField);

$where = $selectQueryBuilder->where('id', 1);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $where);

$whereIn = $selectQueryBuilder->whereIn('id', [1, 2]);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $whereIn);

$selectQueryBuilder->whereGroup(static function ($group): void {
    \PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\WhereGroupBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $group);

    $group->whereGroup(static function ($nestedGroup): void {
        \PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\WhereGroupBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $nestedGroup);
    });

    $group->andWhereGroup(static function ($nestedGroup): void {
        \PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\WhereGroupBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $nestedGroup);
    });

    $group->orWhereGroup(static function ($nestedGroup): void {
        \PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\WhereGroupBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $nestedGroup);
    });
});

\PHPStan\Testing\assertType('Tempest\\Database\\Tests\\QueryStatements\\StubModel|null', $where->first());
\PHPStan\Testing\assertType('array<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $where->all());

$countBuilder = $queryBuilder->count();
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\CountQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $countBuilder);

$countWhere = $countBuilder->whereIn('id', [1, 2]);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\CountQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $countWhere);

$countFromSelect = CountQueryBuilder::fromQueryBuilder($where);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\CountQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $countFromSelect);

$updateBuilder = $queryBuilder->update(name: 'Rudy');
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\UpdateQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $updateBuilder);

$updateWhere = $updateBuilder->whereIn('id', [1, 2]);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\UpdateQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $updateWhere);

$updateFromSelect = UpdateQueryBuilder::fromQueryBuilder($where, name: 'Rudy');
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\UpdateQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $updateFromSelect);

$deleteBuilder = $queryBuilder->delete();
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\DeleteQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $deleteBuilder);

$deleteWhere = $deleteBuilder->whereIn('id', [1, 2]);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\DeleteQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $deleteWhere);

$deleteFromSelect = DeleteQueryBuilder::fromQueryBuilder($where);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\DeleteQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $deleteFromSelect);

$selectFromCount = SelectQueryBuilder::fromQueryBuilder($countBuilder);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', $selectFromCount);

\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\QueryStatements\\StubModel>', StubModel::select());
\PHPStan\Testing\assertType('Tempest\\Database\\Tests\\QueryStatements\\StubModel|null', StubModel::select()->where('id', 1)->first());
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\TypeInference\\ChildStubModel>', ChildStubModel::select());
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\CountQueryBuilder<Tempest\\Database\\Tests\\TypeInference\\ChildStubModel>', ChildStubModel::count());
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<Tempest\\Database\\Tests\\TypeInference\\ChildStubModel>', ChildStubModel::find(id: 1));
\PHPStan\Testing\assertType('Tempest\\Database\\Tests\\TypeInference\\ChildStubModel|null', ChildStubModel::findById(1));
\PHPStan\Testing\assertType('Tempest\\Database\\Tests\\TypeInference\\ChildStubModel|null', ChildStubModel::get(1));
\PHPStan\Testing\assertType('array<Tempest\\Database\\Tests\\TypeInference\\ChildStubModel>', ChildStubModel::all());

$tableQueryBuilder = query('stub_models');
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\QueryBuilder<object>', $tableQueryBuilder);
\PHPStan\Testing\assertType('Tempest\\Database\\Builder\\QueryBuilders\\SelectQueryBuilder<object>', $tableQueryBuilder->select());
\PHPStan\Testing\assertType('object|null', $tableQueryBuilder->select()->where('id', 1)->first());
\PHPStan\Testing\assertType('array<object>', $tableQueryBuilder->select()->where('id', 1)->all());
