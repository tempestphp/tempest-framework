<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Requests;

use Tempest\Database\HasOne;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;
use Tempest\Validation\Rules\HasLength;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;

final class BookRequest implements Request
{
    use IsRequest;

    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Chapter[] */
    public array $chapters = [];

    #[HasOne]
    public ?Isbn $isbn = null;
}
