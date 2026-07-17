<?php

namespace Tests\Tempest\Integration\Database;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tempest\Database\Virtual;
use Tempest\Mapper\SerializeAs;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;
use function Tempest\Database\query;

final class HookedPropertyTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function test_hooked_property_gets_persisted(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModel::class);

        $model = HookedModel::create();

        $this->assertSame(
            'default',
            $model->hooked,
        );

        $this->database->assertTableHasRow(
            'hooked_model',
            hooked: 'default',
        );
    }

    #[Test]
    public function test_hooked_property_can_be_overwritten(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModel::class);

        $model = HookedModel::create(
            hooked: 'other',
        );

        $this->assertSame(
            'other',
            $model->hooked,
        );

        $this->database->assertTableHasRow(
            'hooked_model',
            hooked: 'other',
        );
    }

    #[Test]
    public function test_hooked_property_with_dto(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModelWithKey::class);

        $model = HookedModelWithKey::create();
        $this->assertSame('default', $model->key->value);
    }

    #[Test]
    public function test_hooked_property_with_dto_via_trait(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModelWithTrait::class);

        $model = HookedModelWithTrait::create();
        $this->assertSame('default', $model->key->value);
    }

    #[Test]
    public function test_hooked_property_with_dto_and_provided_value(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModelWithKey::class);

        $model = HookedModelWithKey::create(
            key: new Key('other'),
        );

        $this->assertSame('other', $model->key->value);
    }

    #[Test]
    public function test_hooked_property_with_set_hook(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModelWithKey::class);

        $model = HookedModelWithKey::create(
            key: 'other',
        );

        $this->assertSame('other', $model->key->value);
    }

    #[Test]
    public function test_with_inspect(): void
    {
        $values = inspect(new HookedModel())->getPropertyValues();

        $this->assertSame(['hooked' => 'default'], $values);
    }

    #[Test]
    public function test_with_create_query_builder(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModel::class);

        $model = query(HookedModel::class)->create(
            hooked: 'other',
        );

        $this->assertSame('other', $model->hooked);

        $this->database->assertTableHasRow(
            'hooked_model',
            hooked: 'other',
        );
    }

    #[Test]
    public function test_default_with_create_query_builder(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModel::class);

        $model = query(HookedModel::class)->create();

        $this->assertSame('default', $model->hooked);

        $this->database->assertTableHasRow(
            'hooked_model',
            hooked: 'default',
        );
    }

    #[Test]
    public function test_with_update_query_builder(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, HookedModel::class);

        $model = query(HookedModel::class)->create();

        $model->update(
            hooked: 'other',
        );

        $this->assertSame('other', $model->hooked);

        $this->database->assertTableHasRow(
            'hooked_model',
            hooked: 'other',
        );
    }
}

#[Table('hooked_model')]
class HookedModel implements MigratesUp
{
    use IsDatabaseModel;

    public string $hooked {
        get {
            $this->hooked ??= 'default';

            return $this->hooked;
        }
        set(string $hooked) {
            $this->hooked = $hooked;
        }
    }

    #[Virtual]
    public string $name = 'hooked_model';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('hooked_model')
            ->primary()
            ->string('hooked');
    }
}

#[SerializeAs('key')]
class Key
{
    public function __construct(
        public string $value,
    ) {}
}

#[Table('hooked_model_with_key')]
class HookedModelWithKey implements MigratesUp
{
    use IsDatabaseModel;

    public Key $key {
        get {
            $this->key ??= new Key('default');

            return $this->key;
        }
        set(string|Key $value) {
            if (! $value instanceof Key) {
                $value = new Key($value);
            }

            $this->key = $value;
        }
    }

    #[Virtual]
    public string $name = 'hooked_model_with_key';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('hooked_model_with_key')
            ->primary()
            ->dto('key');
    }
}

trait HasKey
{
    public Key $key {
        get {
            $this->key ??= new Key('default');

            return $this->key;
        }
        set(string|Key $value) {
            if (! $value instanceof Key) {
                $value = new Key($value);
            }

            $this->key = $value;
        }
    }
}

#[Table('hooked_model_with_trait')]
class HookedModelWithTrait implements MigratesUp
{
    use IsDatabaseModel;
    use HasKey;

    #[Virtual]
    public string $name = 'hooked_model_with_trait';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('hooked_model_with_trait')
            ->primary()
            ->dto('key');
    }
}
