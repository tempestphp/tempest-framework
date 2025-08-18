<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\ManyToMany;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Mappers\SelectModelMapper;
use Tempest\Support\Arr\MutableArray;
use Tests\Tempest\Fixtures\Models\ManyToMany\Author;
use Tests\Tempest\Fixtures\Models\ManyToMany\Book;
use function Tempest\Database\inspect;

final class PivotDataMappingTest extends TestCase
{
    private SelectModelMapper $mapper;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new SelectModelMapper();
    }
    
    public function test_pivot_data_is_mapped_correctly(): void
    {
        $data = new MutableArray();
        $model = inspect(Author::class);
        
        // Simulate database row with pivot data
        $row = [
            'authors.id' => 1,
            'authors.name' => 'Jane Doe',
            'authors.email' => 'jane@example.com',
            'books.id' => 10,
            'books.title' => 'PHP Guide',
            'books.isbn' => '978-1234567890',
            'books.pivot.created_at' => '2024-01-01 00:00:00',
            'books.pivot.notes' => 'Primary author',
        ];
        
        $normalized = $this->mapper->normalizeRow($model, $row, $data);
        
        // Check main model data
        $this->assertEquals(1, $normalized['id']);
        $this->assertEquals('Jane Doe', $normalized['name']);
        $this->assertEquals('jane@example.com', $normalized['email']);
        
        // Check related model data
        $this->assertArrayHasKey('books', $normalized);
        $this->assertArrayHasKey(10, $normalized['books']);
        $this->assertEquals(10, $normalized['books'][10]['id']);
        $this->assertEquals('PHP Guide', $normalized['books'][10]['title']);
        
        // Check pivot data
        $this->assertArrayHasKey('pivot', $normalized['books'][10]);
        $this->assertEquals('2024-01-01 00:00:00', $normalized['books'][10]['pivot']['created_at']);
        $this->assertEquals('Primary author', $normalized['books'][10]['pivot']['notes']);
    }
    
    public function test_multiple_pivot_relationships_mapped_correctly(): void
    {
        $data = new MutableArray();
        $model = inspect(Author::class);
        
        // Simulate database rows with multiple relationships
        $rows = [
            [
                'authors.id' => 1,
                'authors.name' => 'Jane Doe',
                'books.id' => 10,
                'books.title' => 'PHP Guide',
                'books.pivot.created_at' => '2024-01-01',
                'books.pivot.notes' => 'Primary',
            ],
            [
                'authors.id' => 1,
                'authors.name' => 'Jane Doe',
                'books.id' => 11,
                'books.title' => 'JS Guide',
                'books.pivot.created_at' => '2024-01-02',
                'books.pivot.notes' => 'Co-author',
            ],
        ];
        
        foreach ($rows as $row) {
            $this->mapper->normalizeRow($model, $row, $data);
        }
        
        $normalized = $data->toArray();
        
        // Should have both books
        $this->assertCount(2, $normalized['books']);
        $this->assertArrayHasKey(10, $normalized['books']);
        $this->assertArrayHasKey(11, $normalized['books']);
        
        // Each should have its own pivot data
        $this->assertEquals('Primary', $normalized['books'][10]['pivot']['notes']);
        $this->assertEquals('Co-author', $normalized['books'][11]['pivot']['notes']);
    }
    
    public function test_has_many_with_pivot_mapping(): void
    {
        $data = new MutableArray();
        $model = inspect(Book::class);
        
        // Simulate database row with HasMany pivot data
        $row = [
            'books.id' => 1,
            'books.title' => 'PHP Guide',
            'categories.id' => 20,
            'categories.name' => 'Programming',
            'categories.pivot.assigned_at' => '2024-03-01',
            'categories.pivot.is_primary' => true,
        ];
        
        $normalized = $this->mapper->normalizeRow($model, $row, $data);
        
        // Check book data
        $this->assertEquals(1, $normalized['id']);
        $this->assertEquals('PHP Guide', $normalized['title']);
        
        // Check category with pivot
        $this->assertArrayHasKey('categories', $normalized);
        $this->assertArrayHasKey(20, $normalized['categories']);
        $this->assertEquals('Programming', $normalized['categories'][20]['name']);
        
        // Check pivot data from HasMany
        $this->assertArrayHasKey('pivot', $normalized['categories'][20]);
        $this->assertEquals('2024-03-01', $normalized['categories'][20]['pivot']['assigned_at']);
        $this->assertTrue($normalized['categories'][20]['pivot']['is_primary']);
    }
    
    public function test_custom_pivot_keys_mapped_correctly(): void
    {
        $data = new MutableArray();
        $model = inspect(Author::class);
        
        // Simulate database row with custom pivot keys
        $row = [
            'authors.id' => 1,
            'authors.name' => 'Jane Doe',
            'tags.id' => 30,
            'tags.name' => 'Fiction',
            'tags.color' => 'blue',
            'tags.pivot.assigned_at' => '2024-02-01',
            'tags.pivot.priority' => 1,
        ];
        
        $normalized = $this->mapper->normalizeRow($model, $row, $data);
        
        // Check tag data
        $this->assertArrayHasKey('tags', $normalized);
        $this->assertArrayHasKey(30, $normalized['tags']);
        $this->assertEquals('Fiction', $normalized['tags'][30]['name']);
        $this->assertEquals('blue', $normalized['tags'][30]['color']);
        
        // Check custom pivot fields
        $this->assertArrayHasKey('pivot', $normalized['tags'][30]);
        $this->assertEquals('2024-02-01', $normalized['tags'][30]['pivot']['assigned_at']);
        $this->assertEquals(1, $normalized['tags'][30]['pivot']['priority']);
    }
    
    public function test_empty_pivot_fields_handled_correctly(): void
    {
        $data = new MutableArray();
        $model = inspect(Author::class);
        
        // Row without pivot data
        $row = [
            'authors.id' => 1,
            'authors.name' => 'Jane Doe',
            'books.id' => 10,
            'books.title' => 'PHP Guide',
            // No pivot fields
        ];
        
        $normalized = $this->mapper->normalizeRow($model, $row, $data);
        
        // Should still have the relationship
        $this->assertArrayHasKey('books', $normalized);
        $this->assertArrayHasKey(10, $normalized['books']);
        
        // Pivot should be empty or not set
        if (isset($normalized['books'][10]['pivot'])) {
            $this->assertEmpty($normalized['books'][10]['pivot']);
        }
    }
    
    public function test_null_pivot_values_handled(): void
    {
        $data = new MutableArray();
        $model = inspect(Author::class);
        
        // Row with null pivot values
        $row = [
            'authors.id' => 1,
            'authors.name' => 'Jane Doe',
            'books.id' => 10,
            'books.title' => 'PHP Guide',
            'books.pivot.created_at' => '2024-01-01',
            'books.pivot.notes' => null, // NULL value
        ];
        
        $normalized = $this->mapper->normalizeRow($model, $row, $data);
        
        // Pivot should contain null value
        $this->assertArrayHasKey('pivot', $normalized['books'][10]);
        $this->assertArrayHasKey('notes', $normalized['books'][10]['pivot']);
        $this->assertNull($normalized['books'][10]['pivot']['notes']);
    }
    
    public function test_nested_pivot_path_handling(): void
    {
        $data = new MutableArray();
        $model = inspect(Author::class);
        
        // Test that 'pivot' is correctly identified as a special path segment
        $row = [
            'authors.id' => 1,
            'authors.name' => 'Jane Doe',
            'books.id' => 10,
            'books.title' => 'PHP Guide',
            'books.pivot.created_at' => '2024-01-01',
            'books.pivot.notes' => 'Test note',
            // This should be treated as pivot data, not a nested relation
            'books.pivot.extra_field' => 'Extra data',
        ];
        
        $normalized = $this->mapper->normalizeRow($model, $row, $data);
        
        // All pivot fields should be under the same pivot key
        $pivot = $normalized['books'][10]['pivot'];
        $this->assertArrayHasKey('created_at', $pivot);
        $this->assertArrayHasKey('notes', $pivot);
        $this->assertArrayHasKey('extra_field', $pivot);
        $this->assertEquals('Extra data', $pivot['extra_field']);
    }
}
