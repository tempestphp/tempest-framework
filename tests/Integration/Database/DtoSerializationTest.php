<?php

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\DatabaseMigration;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Mapper\Casters\DtoCaster;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\SerializeAs;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tempest\Mapper\SerializeWith;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class DtoSerializationTest extends FrameworkIntegrationTestCase
{
    public function test_serializing_object_with_serialization_name(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements DatabaseMigration {
            public string $name = '000_model_with_serializable_object';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('model_with_settings')
                    ->primary()
                    ->json('settings');
            }

            public function down(): null
            {
                return null;
            }
        });

        query(ModelWithSettings::class)
            ->insert(new ModelWithSettings(new Settings(Theme::DARK)))
            ->execute();

        $model = query(ModelWithSettings::class)
            ->select()
            ->first();

        $this->assertSame(Theme::DARK, $model->settings->theme);
    }
}

enum Theme: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
}

final class ModelWithSettings
{
    public function __construct(
        public Settings $settings,
    ) {}
}

#[CastWith(DtoCaster::class)]
#[SerializeWith(DtoSerializer::class)]
#[SerializeAs('settings')]
final class Settings
{
    public function __construct(
        public Theme $theme,
    ) {}
}
