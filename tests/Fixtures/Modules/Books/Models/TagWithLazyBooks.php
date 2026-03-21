<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\BelongsToMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Lazy;
use Tempest\Database\Table;

#[Table(name: 'tags')]
final class TagWithLazyBooks
{
    use IsDatabaseModel;

    public string $label;

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Book[] */
    #[BelongsToMany]
    #[Lazy]
    public array $books = [];
}
