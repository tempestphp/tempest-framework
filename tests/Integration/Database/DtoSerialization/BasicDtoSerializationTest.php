<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\DtoSerialization;

use Tempest\Database\MigratesUp;
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

final class BasicDtoSerializationTest extends FrameworkIntegrationTestCase
{
    public function test_simple_dto_serialization(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '001_simple_character';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('characters')
                    ->primary()
                    ->text('name')
                    ->json('stats');
            }
        });

        $stats = new CharacterStats(level: 50, health: 100, mana: 80);
        $character = new Character('Frieren', $stats);

        query(Character::class)
            ->insert($character)
            ->execute();

        $retrievedCharacter = query(Character::class)
            ->select()
            ->first();

        $this->assertSame('Frieren', $retrievedCharacter->name);
        $this->assertInstanceOf(CharacterStats::class, $retrievedCharacter->stats);
        $this->assertSame(50, $retrievedCharacter->stats->level);
        $this->assertSame(100, $retrievedCharacter->stats->health);
        $this->assertSame(80, $retrievedCharacter->stats->mana);
    }

    public function test_simple_dto_serialization_with_named_arguments(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '001_simple_character_named_args';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('characters')
                    ->primary()
                    ->text('name')
                    ->json('stats');
            }
        });

        query(Character::class)
            ->insert(
                name: 'Fern',
                stats: new CharacterStats(level: 25, health: 80, mana: 120),
            )
            ->execute();

        $retrievedCharacter = query(Character::class)
            ->select()
            ->first();

        $this->assertSame('Fern', $retrievedCharacter->name);
        $this->assertInstanceOf(CharacterStats::class, $retrievedCharacter->stats);
        $this->assertSame(25, $retrievedCharacter->stats->level);
        $this->assertSame(80, $retrievedCharacter->stats->health);
        $this->assertSame(120, $retrievedCharacter->stats->mana);
    }

    public function test_dto_with_enums(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '002_character_class_infos';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('character_class_infos')
                    ->primary()
                    ->text('name')
                    ->json('details');
            }
        });

        $details = new ClassDetails(
            type: CharacterClass::MAGE,
            specialization: MageSpecialization::DESTRUCTION,
            rank: ClassRank::MASTER,
        );

        $characterClass = new CharacterClassInfo('Frieren', $details);

        query(CharacterClassInfo::class)
            ->insert($characterClass)
            ->execute();

        $retrieved = query(CharacterClassInfo::class)
            ->select()
            ->first();

        $this->assertSame('Frieren', $retrieved->name);
        $this->assertSame(CharacterClass::MAGE, $retrieved->details->type);
        $this->assertSame(MageSpecialization::DESTRUCTION, $retrieved->details->specialization);
        $this->assertSame(ClassRank::MASTER, $retrieved->details->rank);
    }

    public function test_dto_with_custom_serialization_name(): void
    {
        $this->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '003_settings';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('user_preferences')
                    ->primary()
                    ->text('username')
                    ->json('settings');
            }
        });

        $preferences = new UserPreferences(
            username: 'frieren',
            settings: new ApplicationSettings(theme: Theme::DARK, notifications: true),
        );

        query(UserPreferences::class)
            ->insert($preferences)
            ->execute();

        $retrieved = query(UserPreferences::class)
            ->select()
            ->first();

        $this->assertSame('frieren', $retrieved->username);
        $this->assertSame(Theme::DARK, $retrieved->settings->theme);
        $this->assertTrue($retrieved->settings->notifications);
    }
}

enum CharacterClass: string
{
    case MAGE = 'mage';
    case WARRIOR = 'warrior';
    case ARCHER = 'archer';
    case PRIEST = 'priest';
}

enum MageSpecialization: string
{
    case DESTRUCTION = 'destruction';
    case RESTORATION = 'restoration';
    case ILLUSION = 'illusion';
    case CONJURATION = 'conjuration';
}

enum ClassRank: string
{
    case NOVICE = 'novice';
    case ADEPT = 'adept';
    case EXPERT = 'expert';
    case MASTER = 'master';
    case LEGENDARY = 'legendary';
}

enum Theme: string
{
    case LIGHT = 'light';
    case DARK = 'dark';
}

final class Character
{
    public function __construct(
        public string $name,
        public CharacterStats $stats,
    ) {}
}

#[CastWith(DtoCaster::class)]
#[SerializeWith(DtoSerializer::class)]
final class CharacterStats
{
    public function __construct(
        public int $level,
        public int $health,
        public int $mana,
    ) {}
}

final class CharacterClassInfo
{
    public function __construct(
        public string $name,
        public ClassDetails $details,
    ) {}
}

#[CastWith(DtoCaster::class)]
#[SerializeWith(DtoSerializer::class)]
final class ClassDetails
{
    public function __construct(
        public CharacterClass $type,
        public MageSpecialization $specialization,
        public ClassRank $rank,
    ) {}
}

final class UserPreferences
{
    public function __construct(
        public string $username,
        public ApplicationSettings $settings,
    ) {}
}

#[CastWith(DtoCaster::class)]
#[SerializeWith(DtoSerializer::class)]
#[SerializeAs('app-settings')]
final class ApplicationSettings
{
    public function __construct(
        public Theme $theme,
        public bool $notifications,
    ) {}
}
