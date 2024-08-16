<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use Tempest\Validation\Rules\Length;

class Book implements DatabaseModel
{
    use IsDatabaseModel;

    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Chapter[] */
    public array $chapters = [];
}
