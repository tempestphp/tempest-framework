<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tempest\Mapper\SerializeAs;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class UpdateQueryBuilderDtoTest extends FrameworkIntegrationTestCase
{
    public function test_update_with_serialize_as_dto(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '001_create_users_table_for_dto_update';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('users')
                    ->primary()
                    ->string('name')
                    ->dto('settings');
            }
        });

        $user = query(UserWithDtoSettings::class)
            ->create(
                name: 'John',
                settings: new DtoSettings(DtoTheme::LIGHT),
            );

        query(UserWithDtoSettings::class)
            ->update(
                name: 'Jane',
                settings: new DtoSettings(DtoTheme::DARK),
            )
            ->where('id', $user->id)
            ->execute();

        $updatedUser = query(UserWithDtoSettings::class)->get($user->id);

        $this->assertSame('Jane', $updatedUser->name);
        $this->assertInstanceOf(DtoSettings::class, $updatedUser->settings);
        $this->assertSame(DtoTheme::DARK, $updatedUser->settings->theme);
    }
}

enum DtoTheme: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
}

#[Table('users')]
final class UserWithDtoSettings
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public DtoSettings $settings,
    ) {}
}

#[SerializeAs('settings')]
final class DtoSettings
{
    public function __construct(
        private(set) DtoTheme $theme,
    ) {}
}
