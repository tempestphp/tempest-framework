<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\DtoSerialization;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Mapper\Casters\DtoCaster;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tempest\Mapper\SerializeWith;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class TopLevelArraySerializationTest extends FrameworkIntegrationTestCase
{
    public function test_top_level_array_of_simple_dtos_serialization(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements DatabaseMigration {
            public string $name = '001_array_containers';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('array_container_models')
                    ->primary()
                    ->text('name')
                    ->json('data');
            }

            public function down(): null
            {
                return null;
            }
        });

        $arrayData = [
            new SimpleArrayItem('First Item', 100),
            new SimpleArrayItem('Second Item', 200),
            new SimpleArrayItem('Third Item', 300),
        ];

        $container = new ArrayContainerModel('Test Array', $arrayData);

        query(ArrayContainerModel::class)
            ->insert($container)
            ->execute();

        $retrieved = query(ArrayContainerModel::class)
            ->select()
            ->where('name', 'Test Array')
            ->first();

        $this->assertInstanceOf(ArrayContainerModel::class, $retrieved);
        $this->assertEquals('Test Array', $retrieved->name);
        $this->assertCount(3, $retrieved->data);

        foreach ($retrieved->data as $index => $item) {
            $this->assertInstanceOf(SimpleArrayItem::class, $item);
            $this->assertEquals($arrayData[$index]->name, $item->name);
            $this->assertEquals($arrayData[$index]->value, $item->value);
        }
    }

    public function test_top_level_array_of_nested_dtos_serialization(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements DatabaseMigration {
            public string $name = '002_array_containers_nested';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('array_container_models')
                    ->primary()
                    ->text('name')
                    ->json('data');
            }

            public function down(): null
            {
                return null;
            }
        });

        $arrayData = [
            new ItemWithNestedArray('Item A', new SimpleArrayItem('Sub A', 50)),
            new ItemWithNestedArray('Item B', new SimpleArrayItem('Sub B', 75)),
        ];

        $container = new ArrayContainerModel('Nested Array', $arrayData);

        query(ArrayContainerModel::class)
            ->insert($container)
            ->execute();

        $retrieved = query(ArrayContainerModel::class)
            ->select()
            ->where('name', 'Nested Array')
            ->first();

        $this->assertInstanceOf(ArrayContainerModel::class, $retrieved);
        $this->assertEquals('Nested Array', $retrieved->name);
        $this->assertCount(2, $retrieved->data);

        foreach ($retrieved->data as $index => $item) {
            $this->assertInstanceOf(ItemWithNestedArray::class, $item);
            $this->assertEquals($arrayData[$index]->name, $item->name);
            $this->assertInstanceOf(SimpleArrayItem::class, $item->item);
            $this->assertEquals($arrayData[$index]->item->name, $item->item->name);
            $this->assertEquals($arrayData[$index]->item->value, $item->item->value);
        }
    }

    public function test_empty_top_level_array(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements DatabaseMigration {
            public string $name = '003_array_containers_empty';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('array_container_models')
                    ->primary()
                    ->text('name')
                    ->json('data');
            }

            public function down(): null
            {
                return null;
            }
        });

        $container = new ArrayContainerModel('Empty Array', []);

        query(ArrayContainerModel::class)
            ->insert($container)
            ->execute();

        $retrieved = query(ArrayContainerModel::class)
            ->select()
            ->where('name', 'Empty Array')
            ->first();

        $this->assertInstanceOf(ArrayContainerModel::class, $retrieved);
        $this->assertEquals('Empty Array', $retrieved->name);
        $this->assertCount(0, $retrieved->data);
    }
}

final readonly class ArrayContainerModel
{
    public function __construct(
        public string $name,
        #[SerializeWith(DtoSerializer::class), CastWith(DtoCaster::class)]
        public array $data,
    ) {}
}

final readonly class SimpleArrayItem
{
    public function __construct(
        public string $name,
        public int $value,
    ) {}
}

final readonly class ItemWithNestedArray
{
    public function __construct(
        public string $name,
        public SimpleArrayItem $item,
    ) {}
}
