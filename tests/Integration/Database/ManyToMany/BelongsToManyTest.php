<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\ManyToMany;

use PHPUnit\Framework\TestCase;
use Tempest\Database\BelongsToMany;
use Tempest\Database\Builder\ModelInspector;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tests\Tempest\Fixtures\Models\ManyToMany\Author;
use Tests\Tempest\Fixtures\Models\ManyToMany\Book;
use Tests\Tempest\Fixtures\Models\ManyToMany\Category;
use Tests\Tempest\Fixtures\Models\ManyToMany\Tag;
use function Tempest\Database\inspect;
use function Tempest\Database\query;

final class BelongsToManyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTables();
        $this->seedData();
    }
    
    protected function tearDown(): void
    {
        $this->dropTables();
        
        parent::tearDown();
    }
    
    public function test_belongs_to_many_relationship_is_detected(): void
    {
        $inspector = inspect(Author::class);
        
        $booksRelation = $inspector->getBelongsToMany('books');
        $this->assertInstanceOf(BelongsToMany::class, $booksRelation);
        $this->assertEquals('books', $booksRelation->name);
        
        $tagsRelation = $inspector->getBelongsToMany('tags');
        $this->assertInstanceOf(BelongsToMany::class, $tagsRelation);
        $this->assertEquals('tags', $tagsRelation->name);
    }
    
    public function test_default_pivot_table_naming(): void
    {
        $author = new Author();
        $author->name = 'Test Author';
        
        $bookInspector = inspect(Book::class);
        $authorInspector = inspect(Author::class);
        
        $booksRelation = $authorInspector->getBelongsToMany('books');
        
        // Should generate 'author_book' (alphabetical order)
        $pivotTable = $this->getPivotTableName($booksRelation, $authorInspector, $bookInspector);
        $this->assertEquals('author_book', $pivotTable);
    }
    
    public function test_custom_pivot_table_configuration(): void
    {
        $authorInspector = inspect(Author::class);
        $tagsRelation = $authorInspector->getBelongsToMany('tags');
        
        // Check custom pivot table name
        $this->assertNotNull($tagsRelation);
        // The actual pivot table name is set in the attribute
        $reflection = new \ReflectionObject($tagsRelation);
        $pivotTableProp = $reflection->getProperty('pivotTable');
        $pivotTableProp->setAccessible(true);
        $this->assertEquals('author_tags', $pivotTableProp->getValue($tagsRelation));
    }
    
    public function test_loading_belongs_to_many_with_pivot_data(): void
    {
        $author = Author::select()
            ->with('books')
            ->where('name = ?', 'Jane Doe')
            ->first();
            
        $this->assertNotNull($author);
        $this->assertCount(2, $author->books);
        
        // Check first book
        $firstBook = $author->books[0];
        $this->assertContains($firstBook->title, ['PHP Guide', 'JavaScript Basics']);
        
        // Check pivot data
        $pivotData = $firstBook->pivot();
        $this->assertArrayHasKey('created_at', $pivotData);
        $this->assertArrayHasKey('notes', $pivotData);
        $this->assertEquals('2024-01-01 00:00:00', $pivotData['created_at']);
        $this->assertEquals('Primary author', $pivotData['notes']);
    }
    
    public function test_pivot_method_with_specific_fields(): void
    {
        $author = Author::select()
            ->with('books')
            ->where('name = ?', 'Jane Doe')
            ->first();
            
        $firstBook = $author->books[0];
        
        // Get only created_at
        $created = $firstBook->pivot('created_at');
        $this->assertCount(1, $created);
        $this->assertArrayHasKey('created_at', $created);
        $this->assertArrayNotHasKey('notes', $created);
        
        // Get multiple fields
        $data = $firstBook->pivot('created_at', 'notes');
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertArrayHasKey('notes', $data);
    }
    
    public function test_reverse_belongs_to_many_relationship(): void
    {
        $book = Book::select()
            ->with('authors')
            ->where('title = ?', 'PHP Guide')
            ->first();
            
        $this->assertNotNull($book);
        $this->assertCount(2, $book->authors);
        
        $authorNames = array_map(fn($author) => $author->name, $book->authors);
        $this->assertContains('Jane Doe', $authorNames);
        $this->assertContains('John Smith', $authorNames);
        
        // Check pivot data from reverse side
        foreach ($book->authors as $author) {
            $pivotData = $author->pivot();
            $this->assertArrayHasKey('created_at', $pivotData);
            $this->assertArrayHasKey('notes', $pivotData);
        }
    }
    
    public function test_custom_pivot_keys(): void
    {
        $author = Author::select()
            ->with('tags')
            ->where('name = ?', 'Jane Doe')
            ->first();
            
        $this->assertNotNull($author);
        $this->assertCount(2, $author->tags);
        
        $tagNames = array_map(fn($tag) => $tag->name, $author->tags);
        $this->assertContains('Fiction', $tagNames);
        $this->assertContains('Bestseller', $tagNames);
        
        // Check custom pivot fields
        $firstTag = $author->tags[0];
        $pivotData = $firstTag->pivot();
        $this->assertArrayHasKey('assigned_at', $pivotData);
        $this->assertArrayHasKey('priority', $pivotData);
    }
    
    public function test_has_many_with_pivot_table(): void
    {
        $book = Book::select()
            ->with('categories')
            ->where('title = ?', 'PHP Guide')
            ->first();
            
        $this->assertNotNull($book);
        $this->assertCount(2, $book->categories);
        
        $categoryNames = array_map(fn($cat) => $cat->name, $book->categories);
        $this->assertContains('Programming', $categoryNames);
        $this->assertContains('Web Development', $categoryNames);
        
        // Check pivot data with HasMany
        $firstCategory = $book->categories[0];
        $pivotData = $firstCategory->pivot();
        $this->assertArrayHasKey('assigned_at', $pivotData);
        $this->assertArrayHasKey('is_primary', $pivotData);
    }
    
    public function test_multiple_relationships_on_same_model(): void
    {
        $author = Author::select()
            ->with('books', 'tags')
            ->where('name = ?', 'Jane Doe')
            ->first();
            
        $this->assertNotNull($author);
        $this->assertCount(2, $author->books);
        $this->assertCount(2, $author->tags);
        
        // Both relationships should have their own pivot data
        $this->assertNotEmpty($author->books[0]->pivot());
        $this->assertNotEmpty($author->tags[0]->pivot());
    }
    
    public function test_empty_pivot_fields_returns_empty_array(): void
    {
        // Create a relationship without pivot fields
        $author = new Author();
        $book = new Book();
        $book->pivot = []; // No pivot data
        
        $this->assertEquals([], $book->pivot());
        $this->assertEquals([], $book->pivot('any_field'));
    }
    
    public function test_join_statement_generation(): void
    {
        $authorInspector = inspect(Author::class);
        $booksRelation = $authorInspector->getBelongsToMany('books');
        $booksRelation->property = $authorInspector->reflector->getProperty('books');
        
        $joinStatement = $booksRelation->getJoinStatement();
        $joinString = $joinStatement->compile(\Tempest\Database\Config\SQLiteDialect::class);
        
        // Should contain two LEFT JOINs
        $this->assertStringContainsString('LEFT JOIN author_book', $joinString);
        $this->assertStringContainsString('LEFT JOIN books', $joinString);
        $this->assertStringContainsString('author_book.author_id = authors.id', $joinString);
        $this->assertStringContainsString('books.id = author_book.book_id', $joinString);
    }
    
    public function test_select_fields_include_pivot_fields(): void
    {
        $authorInspector = inspect(Author::class);
        $booksRelation = $authorInspector->getBelongsToMany('books');
        $booksRelation->property = $authorInspector->reflector->getProperty('books');
        
        $selectFields = $booksRelation->getSelectFields();
        
        // Should include regular book fields
        $fieldStrings = array_map(fn($field) => $field->compile(\Tempest\Database\Config\SQLiteDialect::class), $selectFields->toArray());
        $this->assertContains('books.id AS "books.id"', $fieldStrings);
        $this->assertContains('books.title AS "books.title"', $fieldStrings);
        
        // Should include pivot fields
        $this->assertContains('author_book.created_at AS "books.pivot.created_at"', $fieldStrings);
        $this->assertContains('author_book.notes AS "books.pivot.notes"', $fieldStrings);
    }
    
    private function createTables(): void
    {
        // Create authors table
        (new CreateTableStatement('authors'))
            ->primary()
            ->text('name')
            ->text('email', nullable: true)
            ->execute();
            
        // Create books table
        (new CreateTableStatement('books'))
            ->primary()
            ->text('title')
            ->text('isbn', nullable: true)
            ->integer('published_year', nullable: true)
            ->execute();
            
        // Create tags table
        (new CreateTableStatement('tags'))
            ->primary()
            ->text('name')
            ->text('color', nullable: true)
            ->execute();
            
        // Create categories table
        (new CreateTableStatement('categories'))
            ->primary()
            ->text('name')
            ->text('description', nullable: true)
            ->execute();
            
        // Create pivot tables
        query('CREATE TABLE author_book (
            author_id INTEGER NOT NULL,
            book_id INTEGER NOT NULL,
            created_at DATETIME,
            notes TEXT,
            PRIMARY KEY (author_id, book_id)
        )')->execute();
        
        query('CREATE TABLE author_tags (
            author_uuid INTEGER NOT NULL,
            tag_uuid INTEGER NOT NULL,
            assigned_at DATETIME,
            priority INTEGER,
            PRIMARY KEY (author_uuid, tag_uuid)
        )')->execute();
        
        query('CREATE TABLE book_category (
            book_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            assigned_at DATETIME,
            is_primary BOOLEAN,
            PRIMARY KEY (book_id, category_id)
        )')->execute();
    }
    
    private function seedData(): void
    {
        // Create authors
        $author1 = Author::create(name: 'Jane Doe', email: 'jane@example.com');
        $author2 = Author::create(name: 'John Smith', email: 'john@example.com');
        
        // Create books
        $book1 = Book::create(title: 'PHP Guide', isbn: '978-1234567890');
        $book2 = Book::create(title: 'JavaScript Basics', isbn: '978-0987654321');
        $book3 = Book::create(title: 'Database Design', isbn: '978-1111111111');
        
        // Create tags
        $tag1 = Tag::create(name: 'Fiction', color: 'blue');
        $tag2 = Tag::create(name: 'Bestseller', color: 'gold');
        
        // Create categories
        $cat1 = Category::create(name: 'Programming', description: 'Programming books');
        $cat2 = Category::create(name: 'Web Development', description: 'Web dev books');
        
        // Create relationships in pivot tables
        query('INSERT INTO author_book (author_id, book_id, created_at, notes) VALUES 
            (?, ?, ?, ?),
            (?, ?, ?, ?),
            (?, ?, ?, ?),
            (?, ?, ?, ?)')
            ->execute(
                $author1->id->toInt(), $book1->id->toInt(), '2024-01-01 00:00:00', 'Primary author',
                $author1->id->toInt(), $book2->id->toInt(), '2024-01-02 00:00:00', 'Co-author',
                $author2->id->toInt(), $book1->id->toInt(), '2024-01-03 00:00:00', 'Co-author',
                $author2->id->toInt(), $book3->id->toInt(), '2024-01-04 00:00:00', 'Primary author'
            );
            
        query('INSERT INTO author_tags (author_uuid, tag_uuid, assigned_at, priority) VALUES 
            (?, ?, ?, ?),
            (?, ?, ?, ?)')
            ->execute(
                $author1->id->toInt(), $tag1->id->toInt(), '2024-02-01 00:00:00', 1,
                $author1->id->toInt(), $tag2->id->toInt(), '2024-02-02 00:00:00', 2
            );
            
        query('INSERT INTO book_category (book_id, category_id, assigned_at, is_primary) VALUES 
            (?, ?, ?, ?),
            (?, ?, ?, ?),
            (?, ?, ?, ?)')
            ->execute(
                $book1->id->toInt(), $cat1->id->toInt(), '2024-03-01 00:00:00', 1,
                $book1->id->toInt(), $cat2->id->toInt(), '2024-03-02 00:00:00', 0,
                $book2->id->toInt(), $cat2->id->toInt(), '2024-03-03 00:00:00', 1
            );
    }
    
    private function dropTables(): void
    {
        (new DropTableStatement('author_book'))->execute();
        (new DropTableStatement('author_tags'))->execute();
        (new DropTableStatement('book_category'))->execute();
        (new DropTableStatement('authors'))->execute();
        (new DropTableStatement('books'))->execute();
        (new DropTableStatement('tags'))->execute();
        (new DropTableStatement('categories'))->execute();
    }
    
    private function getPivotTableName(BelongsToMany $relation, ModelInspector $current, ModelInspector $related): string
    {
        // Access private method via reflection for testing
        $reflection = new \ReflectionObject($relation);
        $method = $reflection->getMethod('getPivotTableName');
        $method->setAccessible(true);
        
        return $method->invoke($relation, $current, $related);
    }
}
