<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models\ManyToMany;

use Tempest\Database\BelongsToMany;
use Tempest\Database\IsDatabaseModel;

final class Author
{
    use IsDatabaseModel;
    
    public string $name;
    
    public ?string $email = null;
    
    /** @var Book[] */
    #[BelongsToMany(pivotFields: ['created_at', 'notes'])]
    public array $books = [];
    
    /** @var Tag[] */
    #[BelongsToMany(
        pivotTable: 'author_tags',
        pivotCurrentKey: 'author_uuid',
        pivotRelatedKey: 'tag_uuid',
        pivotFields: ['assigned_at', 'priority']
    )]
    public array $tags = [];
}
