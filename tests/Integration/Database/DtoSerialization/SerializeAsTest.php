<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\DtoSerialization;

use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\SerializeAs;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class SerializeAsTest extends FrameworkIntegrationTestCase
{
    public function test_serialize_as_simple_object(): void
    {
        $config = $this->container->get(MapperConfig::class);
        $config->serializeAs(SimpleSpell::class, 'simple-spell');

        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '001_spell_library';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('spell_libraries')
                    ->primary()
                    ->text('name')
                    ->json('spell_data');
            }
        });

        $spell = new SimpleSpell(name: 'Zoltraak', element: 'destruction');
        $library = new SpellLibrary(name: "Frieren's Collection", spell_data: $spell);

        query(SpellLibrary::class)
            ->insert($library)
            ->execute();

        $retrieved = query(SpellLibrary::class)
            ->select()
            ->first();

        $this->assertSame("Frieren's Collection", $retrieved->name);
        $this->assertInstanceOf(SimpleSpell::class, $retrieved->spell_data);
        $this->assertSame('Zoltraak', $retrieved->spell_data->name);
        $this->assertSame('destruction', $retrieved->spell_data->element);

        $raw = new Query('SELECT spell_data FROM spell_libraries WHERE id = 1')->fetchFirst();
        $json = json_decode($raw['spell_data'], associative: true);

        $this->assertSame('simple-spell', $json['type']);
        $this->assertArrayHasKey('data', $json);
        $this->assertSame('Zoltraak', $json['data']['name']);
        $this->assertSame('destruction', $json['data']['element']);
    }

    public function test_serialize_as_nested_objects(): void
    {
        $config = $this->container->get(MapperConfig::class);
        $config->serializeAs(MageProfile::class, 'mage-profile');
        $config->serializeAs(SimpleSpell::class, 'simple-spell');

        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '002_mage_profiles';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('mages')
                    ->primary()
                    ->text('name')
                    ->json('profile_data');
            }
        });

        $profile = new MageProfile(
            age: 1000,
            favoriteSpell: new SimpleSpell(name: 'Zoltraak', element: 'destruction'),
        );

        $mage = new Mage(name: 'Frieren', profile_data: $profile);

        query(Mage::class)
            ->insert($mage)
            ->execute();

        $retrieved = query(Mage::class)
            ->select()
            ->first();

        $this->assertSame('Frieren', $retrieved->name);
        $this->assertInstanceOf(MageProfile::class, $retrieved->profile_data);
        $this->assertSame(1000, $retrieved->profile_data->age);
        $this->assertInstanceOf(SimpleSpell::class, $retrieved->profile_data->favoriteSpell);
        $this->assertSame('Zoltraak', $retrieved->profile_data->favoriteSpell->name);

        $raw = new Query('SELECT profile_data FROM mages WHERE id = 1')->fetchFirst();
        $json = json_decode($raw['profile_data'], associative: true);

        $this->assertSame('mage-profile', $json['type']);
        $this->assertSame(1000, $json['data']['age']);
        $this->assertSame('simple-spell', $json['data']['favoriteSpell']['type']);
        $this->assertSame('Zoltraak', $json['data']['favoriteSpell']['data']['name']);
    }

    public function test_serialize_as_with_arrays(): void
    {
        $config = $this->container->get(MapperConfig::class);
        $config->serializeAs(SpellCollection::class, 'spell-collection');
        $config->serializeAs(SimpleSpell::class, 'simple-spell');

        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '003_collections';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('spell_containers')
                    ->primary()
                    ->text('title')
                    ->json('collection_data');
            }
        });

        $collection = new SpellCollection(
            count: 3,
            spells: [
                new SimpleSpell(name: 'Zoltraak', element: 'destruction'),
                new SimpleSpell(name: 'Shield', element: 'protection'),
                new SimpleSpell(name: 'Heal', element: 'restoration'),
            ],
        );

        $container = new SpellContainer(title: 'Basic Spells', collection_data: $collection);

        query(SpellContainer::class)
            ->insert($container)
            ->execute();

        $retrieved = query(SpellContainer::class)
            ->select()
            ->first();

        $this->assertSame('Basic Spells', $retrieved->title);
        $this->assertInstanceOf(SpellCollection::class, $retrieved->collection_data);
        $this->assertSame(3, $retrieved->collection_data->count);
        $this->assertCount(3, $retrieved->collection_data->spells);
        $this->assertInstanceOf(SimpleSpell::class, $retrieved->collection_data->spells[0]);
        $this->assertSame('Zoltraak', $retrieved->collection_data->spells[0]->name);

        $raw = new Query('SELECT collection_data FROM spell_containers WHERE id = 1')->fetchFirst();
        $json = json_decode($raw['collection_data'], associative: true);

        $this->assertSame('spell-collection', $json['type']);
        $this->assertSame(3, $json['data']['count']);
        $this->assertCount(3, $json['data']['spells']);
        $this->assertSame('simple-spell', $json['data']['spells'][0]['type']);
        $this->assertSame('Zoltraak', $json['data']['spells'][0]['data']['name']);
    }

    public function test_serialize_as_without_explicit_casters(): void
    {
        $config = $this->container->get(MapperConfig::class);
        $config->serializeAs(MagicItem::class, 'magic-item');

        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '004_inventory';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('inventories')
                    ->primary()
                    ->text('owner')
                    ->json('item_data');
            }
        });

        $item = new MagicItem(
            name: 'Staff of Power',
            type: ItemType::STAFF,
            enchanted: true,
        );

        $inventory = new Inventory(owner: 'Frieren', item_data: $item);

        query(Inventory::class)
            ->insert($inventory)
            ->execute();

        $retrieved = query(Inventory::class)
            ->select()
            ->first();

        $this->assertSame('Frieren', $retrieved->owner);
        $this->assertInstanceOf(MagicItem::class, $retrieved->item_data);
        $this->assertSame('Staff of Power', $retrieved->item_data->name);
        $this->assertSame(ItemType::STAFF, $retrieved->item_data->type);
        $this->assertTrue($retrieved->item_data->enchanted);

        $raw = new Query('SELECT item_data FROM inventories WHERE id = 1')->fetchFirst();
        $json = json_decode($raw['item_data'], associative: true);

        $this->assertSame('magic-item', $json['type']);
        $this->assertSame('Staff of Power', $json['data']['name']);
        $this->assertSame('staff', $json['data']['type']);
        $this->assertTrue($json['data']['enchanted']);
    }
}

enum ItemType: string
{
    case STAFF = 'staff';
    case WAND = 'wand';
    case RING = 'ring';
}

final class SpellLibrary
{
    public function __construct(
        public string $name,
        public SimpleSpell $spell_data,
    ) {}
}

#[SerializeAs('simple-spell')]
final class SimpleSpell
{
    public function __construct(
        public string $name,
        public string $element,
    ) {}
}

final class Mage
{
    public function __construct(
        public string $name,
        public MageProfile $profile_data,
    ) {}
}

#[SerializeAs('mage-profile')]
final class MageProfile
{
    public function __construct(
        public int $age,
        public SimpleSpell $favoriteSpell,
    ) {}
}

final class SpellContainer
{
    public function __construct(
        public string $title,
        public SpellCollection $collection_data,
    ) {}
}

#[SerializeAs('spell-collection')]
final class SpellCollection
{
    public function __construct(
        public int $count,
        /** @var \Tests\Tempest\Integration\Database\DtoSerialization\SimpleSpell[] */
        public array $spells,
    ) {}
}

final class Inventory
{
    public function __construct(
        public string $owner,
        public MagicItem $item_data,
    ) {}
}

#[SerializeAs('magic-item')]
final class MagicItem
{
    public function __construct(
        public string $name,
        public ItemType $type,
        public bool $enchanted,
    ) {}
}
