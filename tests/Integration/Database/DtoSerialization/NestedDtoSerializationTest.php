<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\DtoSerialization;

use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Mapper\SerializeAs;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class NestedDtoSerializationTest extends FrameworkIntegrationTestCase
{
    public function test_deeply_nested_dtos(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '001_spell_structure';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('spells')
                    ->primary()
                    ->text('name')
                    ->json('structure');
            }
        });

        $structure = new SpellStructure(
            incantation: new Incantation(
                words: 'Zoltraak',
                pronunciation: new Pronunciation(
                    syllables: ['Zol', 'traak'],
                    emphasis: new EmphasisPattern(
                        primary: 'Zol',
                        secondary: 'traak',
                        duration: 2.5,
                    ),
                ),
            ),
            components: new SpellComponents(
                verbal: true,
                somatic: true,
                material: new MaterialComponent(
                    item: 'Crystal Focus',
                    rarity: ComponentRarity::RARE,
                    properties: new ItemProperties(
                        durability: 100,
                        enchantment: EnchantmentLevel::HIGH,
                    ),
                ),
            ),
        );

        $spell = new Spell('Zoltraak', $structure);

        query(Spell::class)
            ->insert($spell)
            ->execute();

        $retrieved = query(Spell::class)
            ->select()
            ->first();

        $this->assertSame('Zoltraak', $retrieved->name);
        $this->assertSame('Zoltraak', $retrieved->structure->incantation->words);
        $this->assertSame(['Zol', 'traak'], $retrieved->structure->incantation->pronunciation->syllables);
        $this->assertSame('Zol', $retrieved->structure->incantation->pronunciation->emphasis->primary);
        $this->assertSame(2.5, $retrieved->structure->incantation->pronunciation->emphasis->duration);
        $this->assertTrue($retrieved->structure->components->verbal);
        $this->assertSame('Crystal Focus', $retrieved->structure->components->material->item);
        $this->assertSame(ComponentRarity::RARE, $retrieved->structure->components->material->rarity);
        $this->assertSame(100, $retrieved->structure->components->material->properties->durability);
        $this->assertSame(EnchantmentLevel::HIGH, $retrieved->structure->components->material->properties->enchantment);
    }

    public function test_nested_dtos_with_mixed_types(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, new class implements MigratesUp {
            public string $name = '002_grimoire';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('grimoires')
                    ->primary()
                    ->text('title')
                    ->json('metadata');
            }
        });

        $metadata = new GrimoireMetadata(
            author: new Author(
                name: 'Serie',
                era: MagicalEra::ANCIENT,
                specializations: ['Destruction', 'Restoration', 'Illusion'],
            ),
            contents: new GrimoireContents(
                spellCount: 1000,
                difficulty: DifficultyLevel::LEGENDARY,
                categories: ['Combat', 'Healing', 'Utility'],
                indexing: new IndexingSystem(
                    method: IndexMethod::HIERARCHICAL,
                    crossReferences: true,
                    searchable: true,
                ),
            ),
            preservation: new PreservationInfo(
                condition: PreservationState::PRISTINE,
                lastMaintenance: '1000 years ago',
                protections: ['Time Stasis', 'Magic Barrier', 'Divine Ward'],
            ),
        );

        $grimoire = new Grimoire('Ancient Spell Compendium', $metadata);

        query(Grimoire::class)
            ->insert($grimoire)
            ->execute();

        $retrieved = query(Grimoire::class)
            ->select()
            ->first();

        $this->assertSame('Ancient Spell Compendium', $retrieved->title);
        $this->assertSame('Serie', $retrieved->metadata->author->name);
        $this->assertSame(MagicalEra::ANCIENT, $retrieved->metadata->author->era);
        $this->assertSame(['Destruction', 'Restoration', 'Illusion'], $retrieved->metadata->author->specializations);
        $this->assertSame(1000, $retrieved->metadata->contents->spellCount);
        $this->assertSame(DifficultyLevel::LEGENDARY, $retrieved->metadata->contents->difficulty);
        $this->assertSame(IndexMethod::HIERARCHICAL, $retrieved->metadata->contents->indexing->method);
        $this->assertTrue($retrieved->metadata->contents->indexing->crossReferences);
        $this->assertSame(PreservationState::PRISTINE, $retrieved->metadata->preservation->condition);
        $this->assertSame(['Time Stasis', 'Magic Barrier', 'Divine Ward'], $retrieved->metadata->preservation->protections);
    }
}

enum ComponentRarity: string
{
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';
}

enum EnchantmentLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case ULTIMATE = 'ultimate';
}

enum MagicalEra: string
{
    case ANCIENT = 'ancient';
    case CLASSICAL = 'classical';
    case MEDIEVAL = 'medieval';
    case MODERN = 'modern';
}

enum DifficultyLevel: string
{
    case BASIC = 'basic';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';
    case MASTER = 'master';
    case LEGENDARY = 'legendary';
}

enum IndexMethod: string
{
    case ALPHABETICAL = 'alphabetical';
    case HIERARCHICAL = 'hierarchical';
    case CATEGORICAL = 'categorical';
    case CHRONOLOGICAL = 'chronological';
}

enum PreservationState: string
{
    case PRISTINE = 'pristine';
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case FAIR = 'fair';
    case POOR = 'poor';
    case DAMAGED = 'damaged';
}

final class Spell
{
    public function __construct(
        public string $name,
        public SpellStructure $structure,
    ) {}
}

#[SerializeAs(self::class)]
final class SpellStructure
{
    public function __construct(
        public Incantation $incantation,
        public SpellComponents $components,
    ) {}
}

#[SerializeAs(self::class)]
final class Incantation
{
    public function __construct(
        public string $words,
        public Pronunciation $pronunciation,
    ) {}
}

#[SerializeAs(self::class)]
final class Pronunciation
{
    public function __construct(
        public array $syllables,
        public EmphasisPattern $emphasis,
    ) {}
}

#[SerializeAs(self::class)]
final class EmphasisPattern
{
    public function __construct(
        public string $primary,
        public string $secondary,
        public float $duration,
    ) {}
}

#[SerializeAs(self::class)]
final class SpellComponents
{
    public function __construct(
        public bool $verbal,
        public bool $somatic,
        public MaterialComponent $material,
    ) {}
}

#[SerializeAs(self::class)]
final class MaterialComponent
{
    public function __construct(
        public string $item,
        public ComponentRarity $rarity,
        public ItemProperties $properties,
    ) {}
}

#[SerializeAs(self::class)]
final class ItemProperties
{
    public function __construct(
        public int $durability,
        public EnchantmentLevel $enchantment,
    ) {}
}

final class Grimoire
{
    public function __construct(
        public string $title,
        public GrimoireMetadata $metadata,
    ) {}
}

#[SerializeAs(self::class)]
final class GrimoireMetadata
{
    public function __construct(
        public Author $author,
        public GrimoireContents $contents,
        public PreservationInfo $preservation,
    ) {}
}

#[SerializeAs(self::class)]
final class Author
{
    public function __construct(
        public string $name,
        public MagicalEra $era,
        public array $specializations,
    ) {}
}

#[SerializeAs(self::class)]
final class GrimoireContents
{
    public function __construct(
        public int $spellCount,
        public DifficultyLevel $difficulty,
        public array $categories,
        public IndexingSystem $indexing,
    ) {}
}

#[SerializeAs(self::class)]
final class IndexingSystem
{
    public function __construct(
        public IndexMethod $method,
        public bool $crossReferences,
        public bool $searchable,
    ) {}
}

#[SerializeAs(self::class)]
final class PreservationInfo
{
    public function __construct(
        public PreservationState $condition,
        public string $lastMaintenance,
        public array $protections,
    ) {}
}
