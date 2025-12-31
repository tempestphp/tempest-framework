<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\BelongsTo;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeInsterted;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeInserted;
use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
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

final class InsertRelationsTest extends FrameworkIntegrationTestCase
{
    public function test_inserting_has_many_with_arrays(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                chapters: [
                    ['title' => 'The Journey Begins'],
                    ['title' => 'Meeting Fern'],
                ],
            )
            ->execute();

        $book = Book::select()->with('chapters')->get($id);

        $this->assertSame('Sousou no Frieren', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('The Journey Begins', $book->chapters[0]->title);
        $this->assertSame('Meeting Fern', $book->chapters[1]->title);
    }

    public function test_inserting_has_one_with_array(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $id = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                isbn: ['value' => '978-4091234567'],
            )
            ->execute();

        $book = Book::select()->with('isbn')->get($id);

        $this->assertSame('Sousou no Frieren', $book->title);
        $this->assertNotNull($book->isbn);
        $this->assertSame('978-4091234567', $book->isbn->value);
    }

    public function test_inserting_has_many_with_objects(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                chapters: [
                    Chapter::new(title: 'Himmel the Hero'),
                    Chapter::new(title: 'Stark the Warrior'),
                ],
            )
            ->execute();

        $book = Book::select()->with('chapters')->get($id);

        $this->assertSame('Sousou no Frieren', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('Himmel the Hero', $book->chapters[0]->title);
        $this->assertSame('Stark the Warrior', $book->chapters[1]->title);
    }

    public function test_inserting_has_one_with_object(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $id = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                isbn: Isbn::new(value: '978-4091234567'),
            )
            ->execute();

        $book = Book::select()->with('isbn')->get($id);

        $this->assertSame('Sousou no Frieren', $book->title);
        $this->assertNotNull($book->isbn);
        $this->assertSame('978-4091234567', $book->isbn->value);
    }

    public function test_inserting_mixed_relations(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $id = query(Book::class)
            ->insert(
                title: 'Sousou no Frieren',
                chapters: [
                    Chapter::new(title: 'Chapter with Object'),
                    ['title' => 'Chapter with Array'],
                ],
                isbn: Isbn::new(value: '978-4091234567'),
            )
            ->execute();

        $book = Book::select()->with('chapters', 'isbn')->get($id);

        $this->assertSame('Sousou no Frieren', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('Chapter with Object', $book->chapters[0]->title);
        $this->assertSame('Chapter with Array', $book->chapters[1]->title);
        $this->assertNotNull($book->isbn);
        $this->assertSame('978-4091234567', $book->isbn->value);
    }

    public function test_inserting_empty_has_many_relation(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(title: 'Empty Book', chapters: [])
            ->execute();

        $book = Book::select()
            ->with('chapters')
            ->get($id);

        $this->assertSame('Empty Book', $book->title);
        $this->assertCount(0, $book->chapters);
    }

    public function test_inserting_large_batch_has_many(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $chapters = [];
        for ($i = 1; $i <= 10; $i++) {
            $chapters[] = ['title' => "Chapter {$i}"];
        }

        $id = query(Book::class)
            ->insert(title: 'Long Story', chapters: $chapters)
            ->execute();

        $book = Book::select()->with('chapters')->get($id);

        $this->assertSame('Long Story', $book->title);
        $this->assertCount(10, $book->chapters);
        $this->assertSame('Chapter 1', $book->chapters[0]->title);
        $this->assertSame('Chapter 10', $book->chapters[9]->title);
    }

    public function test_inserting_has_many_preserves_additional_data(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(
                title: 'Detailed Book',
                chapters: [
                    ['title' => 'Chapter 1', 'contents' => 'Once upon a time...'],
                    ['title' => 'Chapter 2', 'contents' => 'And then...'],
                ],
            )
            ->execute();

        $book = Book::select()->with('chapters')->get($id);

        $this->assertSame('Detailed Book', $book->title);
        $this->assertCount(2, $book->chapters);
        $this->assertSame('Chapter 1', $book->chapters[0]->title);
        $this->assertSame('Once upon a time...', $book->chapters[0]->contents);
        $this->assertSame('Chapter 2', $book->chapters[1]->title);
        $this->assertSame('And then...', $book->chapters[1]->contents);
    }

    public function test_inserting_has_many_with_invalid_array_throws_exception(): void
    {
        $this->expectException(HasManyRelationCouldNotBeInsterted::class);

        query(Book::class)
            ->insert(title: 'Bad Book', chapters: 'not an array')
            ->build();
    }

    public function test_inserting_has_one_with_invalid_type_throws_exception(): void
    {
        $this->expectException(HasOneRelationCouldNotBeInserted::class);

        query(Book::class)
            ->insert(title: 'Bad Book', isbn: 123)
            ->build();
    }

    public function test_relation_insertion_with_mixed_types(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(
                title: 'Mixed Content Book',
                chapters: [
                    Chapter::new(title: 'Object Chapter'),
                    ['title' => 'Array Chapter'],
                    Chapter::new(title: 'Another Object Chapter'),
                ],
            )
            ->execute();

        $book = Book::select()->with('chapters')->get($id);

        $this->assertSame('Mixed Content Book', $book->title);
        $this->assertCount(3, $book->chapters);
        $this->assertSame('Object Chapter', $book->chapters[0]->title);
        $this->assertSame('Array Chapter', $book->chapters[1]->title);
        $this->assertSame('Another Object Chapter', $book->chapters[2]->title);
    }

    public function test_insertion_with_custom_primary_key_names(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateMageTable::class, CreateSpellTable::class);

        $id = query(Mage::class)
            ->insert(
                name: 'Frieren',
                element: 'Time',
                spells: [
                    ['name' => 'Zoltraak', 'type' => 'Offensive'],
                    ['name' => 'Defensive Barrier', 'type' => 'Defensive'],
                ],
            )
            ->execute();

        $mage = Mage::select()
            ->with('spells')
            ->get($id);

        $this->assertSame('Frieren', $mage->name);
        $this->assertSame('Time', $mage->element);
        $this->assertCount(2, $mage->spells);
        $this->assertSame('Zoltraak', $mage->spells[0]->name);
        $this->assertSame('Offensive', $mage->spells[0]->type);
        $this->assertSame('Defensive Barrier', $mage->spells[1]->name);
        $this->assertSame('Defensive', $mage->spells[1]->type);
    }

    public function test_insertion_with_non_standard_relation_names(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePartyTable::class, CreateAdventurerTable::class);

        $id = query(Party::class)
            ->insert(
                name: 'Hero Party',
                quest_type: 'Demon King Defeat',
                members: [
                    ['name' => 'Himmel', 'class' => 'Hero'],
                    ['name' => 'Heiter', 'class' => 'Priest'],
                    ['name' => 'Eisen', 'class' => 'Warrior'],
                    ['name' => 'Frieren', 'class' => 'Mage'],
                ],
            )
            ->execute();

        $party = Party::select()
            ->with('members')
            ->get($id);

        $this->assertSame('Hero Party', $party->name);
        $this->assertSame('Demon King Defeat', $party->quest_type);
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

    public function test_insertion_with_custom_foreign_key_names(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreateMageTable::class, CreateSpellTable::class);

        $spellId = query(Spell::class)
            ->insert(
                name: 'Zoltraak',
                type: 'Offensive',
                creator: [
                    'name' => 'Qual',
                    'element' => 'Darkness',
                ],
            )
            ->execute();

        $spell = Spell::select()->with('creator')->get($spellId);

        $this->assertSame('Zoltraak', $spell->name);
        $this->assertSame('Offensive', $spell->type);
        $this->assertNotNull($spell->creator);
        $this->assertSame('Qual', $spell->creator->name);
        $this->assertSame('Darkness', $spell->creator->element);
    }
}

final class Mage
{
    use IsDatabaseModel;

    public PrimaryKey $mage_uuid;

    /** @var \Tests\Tempest\Integration\Database\Builder\Spell[] */
    #[HasMany(ownerJoin: 'creator_uuid', relationJoin: 'mage_uuid')]
    public array $spells = [];

    public function __construct(
        public string $name,
        public string $element,
    ) {}
}

final class Spell
{
    use IsDatabaseModel;

    public PrimaryKey $spell_id;

    #[BelongsTo(ownerJoin: 'creator_uuid', relationJoin: 'mage_uuid')]
    public ?Mage $creator = null;

    public function __construct(
        public string $name,
        public string $type,
    ) {}
}

final class Party
{
    use IsDatabaseModel;

    public PrimaryKey $party_id;

    /** @var \Tests\Tempest\Integration\Database\Builder\Adventurer[] */
    #[HasMany(ownerJoin: 'party_uuid', relationJoin: 'party_id')]
    public array $members = [];

    public function __construct(
        public string $name,
        public string $quest_type,
    ) {}
}

final class Adventurer
{
    use IsDatabaseModel;

    public PrimaryKey $adventurer_id;

    #[BelongsTo(ownerJoin: 'party_uuid', relationJoin: 'party_id')]
    public ?Party $party = null;

    public function __construct(
        public string $name,
        public string $class,
    ) {}
}

final class CreateMageTable implements MigratesUp
{
    private(set) string $name = '100-create-mage';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('mages')
            ->primary('mage_uuid')
            ->varchar('name')
            ->varchar('element');
    }
}

final class CreateSpellTable implements MigratesUp
{
    private(set) string $name = '101-create-spell';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('spells')
            ->primary('spell_id')
            ->varchar('name')
            ->varchar('type')
            ->belongsTo('spells.creator_uuid', 'mages.mage_uuid', nullable: true);
    }
}

final class CreatePartyTable implements MigratesUp
{
    private(set) string $name = '102-create-party';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('parties')
            ->primary('party_id')
            ->varchar('name')
            ->varchar('quest_type');
    }
}

final class CreateAdventurerTable implements MigratesUp
{
    private(set) string $name = '103-create-adventurer';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('adventurers')
            ->primary('adventurer_id')
            ->varchar('name')
            ->varchar('class')
            ->belongsTo('adventurers.party_uuid', 'parties.party_id', nullable: true);
    }
}
