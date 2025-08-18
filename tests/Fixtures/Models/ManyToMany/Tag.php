<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Models\ManyToMany;

use Tempest\Database\BelongsToMany;
use Tempest\Database\IsDatabaseModel;

final class Tag
{
    use IsDatabaseModel;
    
    public string $name;
    
    public ?string $color = null;
    
    /** @var Author[] */
    #[BelongsToMany(
        pivotTable: 'author_tags',
        pivotCurrentKey: 'tag_uuid',
        pivotRelatedKey: 'author_uuid',
        pivotFields: ['assigned_at', 'priority']
    )]
    public array $authors = [];
}
