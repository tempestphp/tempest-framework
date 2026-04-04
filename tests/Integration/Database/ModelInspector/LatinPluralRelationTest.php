<?php

namespace Tests\Tempest\Integration\Database\ModelInspector;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\HasOne;
use Tempest\Database\HasOneThrough;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;

final class LatinPluralRelationTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function belongs_to_with_latin_plural_property_name(): void
    {
        $model = inspect(model: LatinPluralProductModel::class);
        $relation = $model->getRelation(name: 'product_metadata');

        $this->assertInstanceOf(
            expected: BelongsTo::class,
            actual: $relation,
        );

        $this->assertSame(
            expected: 'LEFT JOIN metadata ON metadata.id = products.product_metadata_id',
            actual: $relation
                ->getJoinStatement()
                ->compile(dialect: DatabaseDialect::SQLITE),
        );
    }

    #[Test]
    public function has_one_with_latin_plural_property_name(): void
    {
        $model = inspect(model: LatinPluralMetadataModel::class);
        $relation = $model->getRelation(name: 'extra_data');

        $this->assertInstanceOf(
            expected: HasOne::class,
            actual: $relation,
        );
    }

    #[Test]
    public function has_one_through_with_latin_plural_property_name(): void
    {
        $model = inspect(model: LatinPluralMetadataModel::class);
        $relation = $model->getRelation(name: 'product_criteria');

        $this->assertInstanceOf(
            expected: HasOneThrough::class,
            actual: $relation,
        );
    }

    #[Test]
    public function latin_plural_belongs_to_uses_fk_in_select_fields(): void
    {
        $model = inspect(model: LatinPluralProductModel::class);
        $fields = $model->getSelectFields()->toArray();

        $this->assertContains(
            needle: 'product_metadata_id',
            haystack: $fields,
        );
        $this->assertNotContains(
            needle: 'product_metadata',
            haystack: $fields,
        );
    }
}

#[Table(name: 'products')]
final class LatinPluralProductModel
{
    public PrimaryKey $id;

    #[BelongsTo]
    public ?LatinPluralMetadataModel $product_metadata = null;

    public string $name;
}

#[Table(name: 'metadata')]
final class LatinPluralMetadataModel
{
    public PrimaryKey $id;

    public string $value;

    #[HasOne]
    public ?LatinPluralExtraDataModel $extra_data = null;

    #[HasOneThrough(through: LatinPluralIntermediateModel::class)]
    public ?LatinPluralCriteriaModel $product_criteria = null;
}

#[Table(name: 'extra_data')]
final class LatinPluralExtraDataModel
{
    public PrimaryKey $id;

    public string $info;
}

#[Table(name: 'intermediate')]
final class LatinPluralIntermediateModel
{
    public PrimaryKey $id;

    public LatinPluralMetadataModel $metadata;

    public string $link;
}

#[Table(name: 'criteria')]
final class LatinPluralCriteriaModel
{
    public PrimaryKey $id;

    public string $label;
}
