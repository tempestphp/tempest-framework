<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models\ManyToMany;

use Tempest\Database\BelongsToMany;
use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;

final class Book
{
    use IsDatabaseModel;
    
    public string $title;
    
    public ?string $isbn = null;
    
    public ?int $publishedYear = null;
    
    /** @var Author[] */
    #[BelongsToMany(pivotFields: ['created_at', 'notes'])]
    public array $authors = [];
    
    /** @var Category[] */
    #[HasMany(
        pivotTable: 'book_category',
        pivotFields: ['assigned_at', 'is_primary']
    )]
    public array $categories = [];
}
