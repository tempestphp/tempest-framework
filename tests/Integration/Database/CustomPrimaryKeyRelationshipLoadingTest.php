<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\BelongsTo;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class CustomPrimaryKeyRelationshipLoadingTest extends FrameworkIntegrationTestCase
{
    public function test_has_one_relationship_with_uuid_primary_keys(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageWithUuidMigration::class,
            CreateGrimoireWithUuidMigration::class,
        );

        $mage = query(MageWithUuid::class)->create(
            name: 'Frieren',
            element: 'Time',
        );

        $grimoire = query(GrimoireWithUuid::class)->create(
            mage_uuid: $mage->uuid->value,
            title: 'Ancient Time Magic Compendium',
            spells_count: 847,
        );

        $this->assertInstanceOf(PrimaryKey::class, $mage->uuid);
        $this->assertInstanceOf(PrimaryKey::class, $grimoire->uuid);

        $loadedMage = query(MageWithUuid::class)->get($mage->uuid);
        $loadedMage->load('grimoire');

        $this->assertInstanceOf(GrimoireWithUuid::class, $loadedMage->grimoire);
        $this->assertSame('Ancient Time Magic Compendium', $loadedMage->grimoire->title);
        $this->assertSame(847, $loadedMage->grimoire->spells_count);
        $this->assertTrue($mage->uuid->equals($loadedMage->uuid));
        $this->assertTrue($grimoire->uuid->equals($loadedMage->grimoire->uuid));
    }

    public function test_has_many_relationship_with_uuid_primary_keys(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageWithUuidMigration::class,
            CreateSpellWithUuidMigration::class,
        );

        $mage = query(MageWithUuid::class)->create(
            name: 'Flamme',
            element: 'Fire',
        );

        $spell1 = query(SpellWithUuid::class)->create(
            mage_uuid: $mage->uuid->value,
            name: 'Zoltraak',
            power_level: 95,
            mana_cost: 150,
        );

        $spell2 = query(SpellWithUuid::class)->create(
            mage_uuid: $mage->uuid->value,
            name: 'Volzandia',
            power_level: 87,
            mana_cost: 120,
        );

        $loadedMage = query(MageWithUuid::class)->get($mage->uuid);
        $loadedMage->load('spells');

        $this->assertCount(2, $loadedMage->spells);

        $spellNames = array_map(fn (SpellWithUuid $spell) => $spell->name, $loadedMage->spells);
        $this->assertContains('Zoltraak', $spellNames);
        $this->assertContains('Volzandia', $spellNames);

        foreach ($loadedMage->spells as $spell) {
            $this->assertInstanceOf(SpellWithUuid::class, $spell);
            $this->assertInstanceOf(PrimaryKey::class, $spell->uuid);
            $this->assertSame($mage->uuid->value, $spell->mage_uuid);
        }
    }

    public function test_belongs_to_relationship_with_uuid_primary_keys(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageWithUuidMigration::class,
            CreateSpellWithUuidMigration::class,
        );

        $mage = query(MageWithUuid::class)->create(
            name: 'Serie',
            element: 'Ancient',
        );

        $spell = query(SpellWithUuid::class)->create(
            mage_uuid: $mage->uuid->value,
            name: 'Goddess Magic',
            power_level: 100,
            mana_cost: 999,
        );

        $loadedSpell = query(SpellWithUuid::class)->get($spell->uuid);
        $loadedSpell->load('mage');

        $this->assertInstanceOf(MageWithUuid::class, $loadedSpell->mage);
        $this->assertSame('Serie', $loadedSpell->mage->name);
        $this->assertSame('Ancient', $loadedSpell->mage->element);
        $this->assertTrue($mage->uuid->equals($loadedSpell->mage->uuid));
    }

    public function test_nested_relationship_loading_with_uuid_primary_keys(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageWithUuidMigration::class,
            CreateGrimoireWithUuidMigration::class,
            CreateSpellWithUuidMigration::class,
        );

        $mage = query(MageWithUuid::class)->create(
            name: 'Fern',
            element: 'Combat',
        );

        $grimoire = query(GrimoireWithUuid::class)->create(
            mage_uuid: $mage->uuid->value,
            title: 'Combat Magic Fundamentals',
            spells_count: 42,
        );

        $spell = query(SpellWithUuid::class)->create(
            mage_uuid: $mage->uuid->value,
            name: 'Basic Attack Magic',
            power_level: 75,
            mana_cost: 50,
        );

        $loadedMage = query(MageWithUuid::class)->get($mage->uuid);
        $loadedMage->load('grimoire', 'spells');

        $this->assertInstanceOf(GrimoireWithUuid::class, $loadedMage->grimoire);
        $this->assertSame('Combat Magic Fundamentals', $loadedMage->grimoire->title);

        $this->assertCount(1, $loadedMage->spells);
        $this->assertSame('Basic Attack Magic', $loadedMage->spells[0]->name);

        $loadedSpell = query(SpellWithUuid::class)->get($spell->uuid);
        $loadedSpell->load('mage.grimoire');

        $this->assertInstanceOf(MageWithUuid::class, $loadedSpell->mage);
        $this->assertInstanceOf(GrimoireWithUuid::class, $loadedSpell->mage->grimoire);
        $this->assertSame('Fern', $loadedSpell->mage->name);
        $this->assertSame('Combat Magic Fundamentals', $loadedSpell->mage->grimoire->title);
    }

    public function test_relationship_with_custom_foreign_key_naming(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageWithUuidMigration::class,
            CreateArtifactWithUuidMigration::class,
        );

        $mage = query(MageWithUuid::class)->create(
            name: 'Himmel',
            element: 'Hero',
        );

        $artifact = query(ArtifactWithUuid::class)->create(
            owner_uuid: $mage->uuid->value,
            name: 'Hero Sword',
            rarity: 'Legendary',
            enchantment_level: 10,
        );

        $loadedMage = query(MageWithUuid::class)->get($mage->uuid);
        $loadedMage->load('artifacts');

        $this->assertCount(1, $loadedMage->artifacts);
        $this->assertSame('Hero Sword', $loadedMage->artifacts[0]->name);
        $this->assertSame('Legendary', $loadedMage->artifacts[0]->rarity);

        $loadedArtifact = query(ArtifactWithUuid::class)->get($artifact->uuid);
        $loadedArtifact->load('owner');

        $this->assertInstanceOf(MageWithUuid::class, $loadedArtifact->owner);
        $this->assertSame('Himmel', $loadedArtifact->owner->name);
        $this->assertTrue($mage->uuid->equals($loadedArtifact->owner->uuid));
    }

    public function test_relationship_loading_preserves_uuid_integrity(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageWithUuidMigration::class,
            CreateSpellWithUuidMigration::class,
        );

        $mage1 = query(MageWithUuid::class)->create(name: 'Stark', element: 'Axe');
        $mage2 = query(MageWithUuid::class)->create(name: 'Eisen', element: 'Monk');

        $spell1 = query(SpellWithUuid::class)->create(
            mage_uuid: $mage1->uuid->value,
            name: 'Axe Technique',
            power_level: 80,
            mana_cost: 30,
        );

        $spell2 = query(SpellWithUuid::class)->create(
            mage_uuid: $mage2->uuid->value,
            name: 'Warrior Meditation',
            power_level: 60,
            mana_cost: 20,
        );

        $loadedMage1 = query(MageWithUuid::class)->get($mage1->uuid);
        $loadedMage1->load('spells');

        $loadedMage2 = query(MageWithUuid::class)->get($mage2->uuid);
        $loadedMage2->load('spells');

        $this->assertCount(1, $loadedMage1->spells);
        $this->assertCount(1, $loadedMage2->spells);

        $this->assertSame('Axe Technique', $loadedMage1->spells[0]->name);
        $this->assertSame('Warrior Meditation', $loadedMage2->spells[0]->name);

        $this->assertSame($mage1->uuid->value, $loadedMage1->spells[0]->mage_uuid);
        $this->assertSame($mage2->uuid->value, $loadedMage2->spells[0]->mage_uuid);

        $this->assertFalse($mage1->uuid->equals($mage2->uuid));
        $this->assertFalse($spell1->uuid->equals($spell2->uuid));
    }

    public function test_automatic_uuid_primary_key_detection(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateMageSimpleMigration::class,
            CreateSpellSimpleMigration::class,
        );

        $mage = query(MageSimple::class)->create(
            name: 'Fern',
            element: 'Combat',
        );

        $spell = query(SpellSimple::class)->create(
            mage_uuid: $mage->uuid->value,
            name: 'Cutting Magic',
            power_level: 90,
        );

        $loadedMage = query(MageSimple::class)->get($mage->uuid);
        $loadedMage->load('spells');

        $this->assertCount(1, $loadedMage->spells);
        $this->assertSame('Cutting Magic', $loadedMage->spells[0]->name);

        $loadedSpell = query(SpellSimple::class)->get($spell->uuid);
        $loadedSpell->load('mage');

        $this->assertInstanceOf(MageSimple::class, $loadedSpell->mage);
        $this->assertSame('Fern', $loadedSpell->mage->name);
    }
}

#[Table('mages_with_uuid')]
final class MageWithUuid
{
    use IsDatabaseModel;

    public PrimaryKey $uuid;

    #[HasOne(ownerJoin: 'mage_uuid')]
    public ?GrimoireWithUuid $grimoire = null;

    /** @var \Tests\Tempest\Integration\Database\SpellWithUuid[] */
    #[HasMany(ownerJoin: 'mage_uuid')]
    public array $spells = [];

    /** @var \Tests\Tempest\Integration\Database\ArtifactWithUuid[] */
    #[HasMany(ownerJoin: 'owner_uuid')]
    public array $artifacts = [];

    public function __construct(
        public string $name,
        public string $element,
    ) {}
}

#[Table('grimoires_with_uuid')]
final class GrimoireWithUuid
{
    use IsDatabaseModel;

    public PrimaryKey $uuid;

    #[HasOne(ownerJoin: 'uuid', relationJoin: 'mage_uuid')]
    public ?MageWithUuid $mage = null;

    public function __construct(
        public int $mage_uuid,
        public string $title,
        public int $spells_count,
    ) {}
}

#[Table('spells_with_uuid')]
final class SpellWithUuid
{
    use IsDatabaseModel;

    public PrimaryKey $uuid;

    #[HasOne(ownerJoin: 'uuid', relationJoin: 'mage_uuid')]
    public ?MageWithUuid $mage = null;

    public function __construct(
        public int $mage_uuid,
        public string $name,
        public int $power_level,
        public int $mana_cost,
    ) {}
}

#[Table('artifacts_with_uuid')]
final class ArtifactWithUuid
{
    use IsDatabaseModel;

    public PrimaryKey $uuid;

    #[HasOne(ownerJoin: 'uuid', relationJoin: 'owner_uuid')]
    public ?MageWithUuid $owner = null;

    public function __construct(
        public int $owner_uuid,
        public string $name,
        public string $rarity,
        public int $enchantment_level,
    ) {}
}

final class CreateMageWithUuidMigration implements MigratesUp
{
    public string $name = '001_create_mages_with_uuid';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(MageWithUuid::class)
            ->primary(name: 'uuid')
            ->text('name')
            ->text('element');
    }
}

final class CreateGrimoireWithUuidMigration implements MigratesUp
{
    public string $name = '002_create_grimoires_with_uuid';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(GrimoireWithUuid::class)
            ->primary(name: 'uuid')
            ->belongsTo('grimoires_with_uuid.mage_uuid', 'mages_with_uuid.uuid')
            ->text('title')
            ->integer('spells_count');
    }
}

final class CreateSpellWithUuidMigration implements MigratesUp
{
    public string $name = '003_create_spells_with_uuid';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(SpellWithUuid::class)
            ->primary(name: 'uuid')
            ->belongsTo('spells_with_uuid.mage_uuid', 'mages_with_uuid.uuid')
            ->text('name')
            ->integer('power_level')
            ->integer('mana_cost');
    }
}

final class CreateArtifactWithUuidMigration implements MigratesUp
{
    public string $name = '004_create_artifacts_with_uuid';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(ArtifactWithUuid::class)
            ->primary(name: 'uuid')
            ->belongsTo('artifacts_with_uuid.owner_uuid', 'mages_with_uuid.uuid')
            ->text('name')
            ->text('rarity')
            ->integer('enchantment_level');
    }
}

#[Table('mages')]
final class MageSimple
{
    use IsDatabaseModel;

    public PrimaryKey $uuid;

    /** @var \Tests\Tempest\Integration\Database\SpellSimple[] */
    #[HasMany]
    public array $spells = [];

    public function __construct(
        public string $name,
        public string $element,
    ) {}
}

#[Table('spells')]
final class SpellSimple
{
    use IsDatabaseModel;

    public PrimaryKey $uuid;

    #[BelongsTo]
    public ?MageSimple $mage = null;

    public function __construct(
        public int $mage_uuid,
        public string $name,
        public int $power_level,
    ) {}
}

final class CreateMageSimpleMigration implements MigratesUp
{
    public string $name = '005_create_mages_simple';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(MageSimple::class)
            ->primary(name: 'uuid')
            ->text('name')
            ->text('element');
    }
}

final class CreateSpellSimpleMigration implements MigratesUp
{
    public string $name = '006_create_spells_simple';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(SpellSimple::class)
            ->primary(name: 'uuid')
            ->belongsTo('spells.mage_uuid', 'mages.uuid')
            ->text('name')
            ->integer('power_level');
    }
}
