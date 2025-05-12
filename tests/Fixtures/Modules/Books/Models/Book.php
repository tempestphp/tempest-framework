<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Router\Bindable;
use Tempest\Validation\Rules\Length;

final class Book implements Bindable
{
    use IsDatabaseModel;

    #[Length(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Chapter[] */
    public array $chapters = [];

    #[HasOne]
    public ?Isbn $isbn = null;
}
