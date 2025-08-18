<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models\ManyToMany;

use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;

final class Category
{
    use IsDatabaseModel;
    
    public string $name;
    
    public ?string $description = null;
    
    /** @var Book[] */
    #[HasMany(
        pivotTable: 'book_category',
        pivotFields: ['assigned_at', 'is_primary']
    )]
    public array $books = [];
}
