<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\BelongsTo;
use Tempest\Database\Exceptions\CouldNotUpdateRelation;
use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\Table;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class UpdateRelationsTest extends FrameworkIntegrationTestCase
{
    public function test_updating_has_many_with_arrays(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $bookId = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                chapters: [
                    ['title' => 'The Journey Begins'],
                    ['title' => 'Meeting Fern'],
                ],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Sousou no Frieren - Updated',
                chapters: [
                    ['title' => 'The New Journey Begins'],
                    ['title' => 'Meeting Stark'],
                    ['title' => 'The Magic Academy'],
                ],
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('chapters')->get($bookId);

        $this->assertSame('Sousou no Frieren - Updated', $book->title);
        $this->assertCount(3, $book->chapters);
        $this->assertSame('The New Journey Begins', $book->chapters[0]->title);
        $this->assertSame('Meeting Stark', $book->chapters[1]->title);
        $this->assertSame('The Magic Academy', $book->chapters[2]->title);
    }

    public function test_updating_has_one_with_array(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $bookId = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                isbn: ['value' => '978-4091234567'],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Sousou no Frieren - Updated',
                isbn: ['value' => '978-4091234568'],
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('isbn')->get($bookId);

        $this->assertSame('Sousou no Frieren - Updated', $book->title);
        $this->assertNotNull($book->isbn);
        $this->assertSame('978-4091234568', $book->isbn->value);
    }

    public function test_updating_has_many_with_objects(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $bookId = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                chapters: [
                    ['title' => 'The Journey Begins'],
                    ['title' => 'Meeting Fern'],
                ],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Sousou no Frieren - Updated',
                chapters: [
                    Chapter::new(title: 'Himmel the Hero'),
                    Chapter::new(title: 'Stark the Warrior'),
                ],
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('chapters')->get($bookId);

        $this->assertSame('Sousou no Frieren - Updated', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('Himmel the Hero', $book->chapters[0]->title);
        $this->assertSame('Stark the Warrior', $book->chapters[1]->title);
    }

    public function test_updating_has_one_with_object(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $bookId = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                isbn: ['value' => '978-4091234567'],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Sousou no Frieren - Updated',
                isbn: Isbn::new(value: '978-4091234568'),
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('isbn')->get($bookId);

        $this->assertSame('978-4091234568', $book->isbn->value);
    }

    public function test_updating_mixed_relations(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $bookId = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                chapters: [
                    ['title' => 'Old Chapter 1'],
                    ['title' => 'Old Chapter 2'],
                ],
                isbn: ['value' => '978-4091234567'],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Sousou no Frieren - Updated',
                chapters: [
                    Chapter::new(title: 'Chapter with Object'),
                    ['title' => 'Chapter with Array'],
                ],
                isbn: Isbn::new(value: '978-4091234568'),
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('chapters', 'isbn')->get($bookId);

        $this->assertSame('Sousou no Frieren - Updated', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('Chapter with Object', $book->chapters[0]->title);
        $this->assertSame('Chapter with Array', $book->chapters[1]->title);
        $this->assertNotNull($book->isbn);
        $this->assertSame('978-4091234568', $book->isbn->value);
    }

    public function test_updating_empty_has_many_relation(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $bookId = query(Book::class)
            ->insert(
                title: 'Book with Chapters',
                chapters: [
                    ['title' => 'Chapter 1'],
                    ['title' => 'Chapter 2'],
                ],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Empty Book',
                chapters: [],
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()
            ->with('chapters')
            ->get($bookId);

        $this->assertSame('Empty Book', $book->title);
        $this->assertCount(0, $book->chapters);
    }

    public function test_updating_large_batch_has_many(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $bookId = query(Book::class)
            ->insert(
                title: 'Short Story',
                chapters: [
                    ['title' => 'Chapter 1'],
                ],
            )
            ->execute();

        $chapters = [];
        for ($i = 1; $i <= 10; $i++) {
            $chapters[] = ['title' => "Updated Chapter {$i}"];
        }

        query(Book::class)
            ->update(
                title: 'Long Story',
                chapters: $chapters,
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('chapters')->get($bookId);

        $this->assertSame('Long Story', $book->title);
        $this->assertCount(10, $book->chapters);
        $this->assertSame('Updated Chapter 1', $book->chapters[0]->title);
        $this->assertSame('Updated Chapter 10', $book->chapters[9]->title);
    }

    public function test_updating_has_many_preserves_additional_data(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $bookId = query(Book::class)
            ->insert(
                title: 'Simple Book',
                chapters: [
                    ['title' => 'Chapter 1'],
                ],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Detailed Book',
                chapters: [
                    ['title' => 'Chapter 1', 'contents' => 'Once upon a time...'],
                    ['title' => 'Chapter 2', 'contents' => 'And then...'],
                ],
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('chapters')->get($bookId);

        $this->assertSame('Detailed Book', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('Chapter 1', $book->chapters[0]->title);
        $this->assertSame('Once upon a time...', $book->chapters[0]->contents);
        $this->assertSame('Chapter 2', $book->chapters[1]->title);
        $this->assertSame('And then...', $book->chapters[1]->contents);
    }

    public function test_updating_relation_with_mixed_types(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $bookId = query(Book::class)
            ->insert(
                title: 'Original Book',
                chapters: [
                    ['title' => 'Old Chapter'],
                ],
            )
            ->execute();

        query(Book::class)
            ->update(
                title: 'Mixed Content Book',
                chapters: [
                    Chapter::new(title: 'Object Chapter'),
                    ['title' => 'Array Chapter'],
                    Chapter::new(title: 'Another Object Chapter'),
                ],
            )
            ->where('id', $bookId)
            ->execute();

        $book = Book::select()->with('chapters')->get($bookId);

        $this->assertSame('Mixed Content Book', $book->title);
        $this->assertCount(3, $book->chapters);
        $this->assertSame('Object Chapter', $book->chapters[0]->title);
        $this->assertSame('Array Chapter', $book->chapters[1]->title);
        $this->assertSame('Another Object Chapter', $book->chapters[2]->title);
    }

    public function test_updating_with_custom_primary_key_names(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUpdateMageTable::class, CreateUpdateSpellTable::class);

        $mageId = query(UpdateMage::class)
            ->insert(
                name: 'Frieren',
                element: 'Time',
                spells: [
                    ['name' => 'Zoltraak', 'type' => 'Offensive'],
                ],
            )
            ->execute();

        query(UpdateMage::class)
            ->update(
                name: 'Frieren the Slayer',
                element: 'Time Magic',
                spells: [
                    ['name' => 'Zoltraak', 'type' => 'Offensive'],
                    ['name' => 'Defensive Barrier', 'type' => 'Defensive'],
                ],
            )
            ->where('mage_uuid', $mageId)
            ->execute();

        $mage = UpdateMage::select()
            ->with('spells')
            ->get($mageId);

        $this->assertSame('Frieren the Slayer', $mage->name);
        $this->assertSame('Time Magic', $mage->element);
        $this->assertCount(2, $mage->spells);
        $this->assertSame('Zoltraak', $mage->spells[0]->name);
        $this->assertSame('Offensive', $mage->spells[0]->type);
        $this->assertSame('Defensive Barrier', $mage->spells[1]->name);
        $this->assertSame('Defensive', $mage->spells[1]->type);
    }

    public function test_updating_with_non_standard_relation_names(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUpdatePartyTable::class, CreateUpdateAdventurerTable::class);

        $partyId = query(UpdateParty::class)
            ->insert(
                name: 'Hero Party',
                quest_type: 'Demon King Defeat',
                members: [
                    ['name' => 'Himmel', 'class' => 'Hero'],
                    ['name' => 'Heiter', 'class' => 'Priest'],
                ],
            )
            ->execute();

        query(UpdateParty::class)
            ->update(
                name: 'Legendary Hero Party',
                quest_type: 'Demon King Conquest',
                members: [
                    ['name' => 'Himmel', 'class' => 'Hero'],
                    ['name' => 'Heiter', 'class' => 'Priest'],
                    ['name' => 'Eisen', 'class' => 'Warrior'],
                    ['name' => 'Frieren', 'class' => 'Mage'],
                ],
            )
            ->where('party_id', $partyId)
            ->execute();

        $party = UpdateParty::select()
            ->with('members')
            ->get($partyId);

        $this->assertSame('Legendary Hero Party', $party->name);
        $this->assertSame('Demon King Conquest', $party->quest_type);
        $this->assertCount(4, $party->members);
        $this->assertSame('Himmel', $party->members[0]->name);
        $this->assertSame('Hero', $party->members[0]->class);
        $this->assertSame('Heiter', $party->members[1]->name);
        $this->assertSame('Priest', $party->members[1]->class);
        $this->assertSame('Eisen', $party->members[2]->name);
        $this->assertSame('Warrior', $party->members[2]->class);
        $this->assertSame('Frieren', $party->members[3]->name);
        $this->assertSame('Mage', $party->members[3]->class);
    }

    public function test_updating_with_custom_foreign_key_names(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateUpdateMageTable::class, CreateUpdateSpellTable::class);

        $spellId = query(UpdateSpell::class)
            ->insert(
                name: 'Zoltraak',
                type: 'Offensive',
                creator: [
                    'name' => 'Qual',
                    'element' => 'Darkness',
                ],
            )
            ->execute();

        query(UpdateSpell::class)
            ->update(
                name: 'Enhanced Zoltraak',
                type: 'Advanced Offensive',
                creator: [
                    'name' => 'Qual the Demon',
                    'element' => 'Dark Magic',
                ],
            )
            ->where('spell_id', $spellId)
            ->execute();

        $spell = UpdateSpell::select()->with('creator')->get($spellId);

        $this->assertSame('Enhanced Zoltraak', $spell->name);
        $this->assertSame('Advanced Offensive', $spell->type);
        $this->assertNotNull($spell->creator);
        $this->assertSame('Qual the Demon', $spell->creator->name);
        $this->assertSame('Dark Magic', $spell->creator->element);
    }

    public function test_update_throws_exception_when_model_has_no_primary_key(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateSimpleModelWithoutPrimaryKeyTable::class,
        );

        query(SimpleModelWithoutPrimaryKey::class)
            ->insert(name: 'initial')
            ->execute();

        $this->expectException(CouldNotUpdateRelation::class);

        query(SimpleModelWithoutPrimaryKey::class)
            ->update(
                name: 'updated',
                chapters: [['title' => 'test chapter']],
            )
            ->where('name', 'initial')
            ->execute();
    }
}

#[Table('mages')]
final class UpdateMage
{
    use IsDatabaseModel;

    public PrimaryKey $mage_uuid;

    /** @var \Tests\Tempest\Integration\Database\Builder\UpdateSpell[] */
    #[HasMany(ownerJoin: 'creator_uuid', relationJoin: 'mage_uuid')]
    public array $spells = [];

    public function __construct(
        public string $name,
        public string $element,
    ) {}
}

#[Table('spells')]
final class UpdateSpell
{
    use IsDatabaseModel;

    public PrimaryKey $spell_id;

    #[BelongsTo(ownerJoin: 'creator_uuid', relationJoin: 'mage_uuid')]
    public ?UpdateMage $creator = null;

    public function __construct(
        public string $name,
        public string $type,
    ) {}
}

#[Table('parties')]
final class UpdateParty
{
    use IsDatabaseModel;

    public PrimaryKey $party_id;

    /** @var \Tests\Tempest\Integration\Database\Builder\UpdateAdventurer[] */
    #[HasMany(ownerJoin: 'party_uuid', relationJoin: 'party_id')]
    public array $members = [];

    public function __construct(
        public string $name,
        public string $quest_type,
    ) {}
}

#[Table('adventurers')]
final class UpdateAdventurer
{
    use IsDatabaseModel;

    public PrimaryKey $adventurer_id;

    #[BelongsTo(ownerJoin: 'party_uuid', relationJoin: 'party_id')]
    public ?UpdateParty $party = null;

    public function __construct(
        public string $name,
        public string $class,
    ) {}
}

final class CreateUpdateMageTable implements MigratesUp
{
    private(set) string $name = '100-create-update-mage';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('mages')
            ->primary('mage_uuid')
            ->varchar('name')
            ->varchar('element');
    }
}

final class CreateUpdateSpellTable implements MigratesUp
{
    private(set) string $name = '101-create-update-spell';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('spells')
            ->primary('spell_id')
            ->varchar('name')
            ->varchar('type')
            ->belongsTo('spells.creator_uuid', 'mages.mage_uuid', nullable: true);
    }
}

final class CreateUpdatePartyTable implements MigratesUp
{
    private(set) string $name = '102-create-update-party';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('parties')
            ->primary('party_id')
            ->varchar('name')
            ->varchar('quest_type');
    }
}

final class CreateUpdateAdventurerTable implements MigratesUp
{
    private(set) string $name = '103-create-update-adventurer';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('adventurers')
            ->primary('adventurer_id')
            ->varchar('name')
            ->varchar('class')
            ->belongsTo('adventurers.party_uuid', 'parties.party_id', nullable: true);
    }
}

#[Table('simple_no_pk')]
final class SimpleModelWithoutPrimaryKey
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Chapter[] */
        public array $chapters = [],
    ) {}
}

final class CreateSimpleModelWithoutPrimaryKeyTable implements MigratesUp
{
    private(set) string $name = '106-create-simple-no-pk-table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('simple_no_pk')
            ->varchar('name');
    }
}
