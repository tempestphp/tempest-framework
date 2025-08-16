<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\PrimaryKey;
use Tempest\Router\Bindable;
use Tempest\Validation\Rules\HasLength;
use Tempest\Validation\SkipValidation;

final class Book implements Bindable
{
    use IsDatabaseModel;

    #[SkipValidation]
    public PrimaryKey $id;

    #[HasLength(min: 1, max: 120)]
    public string $title;

    public ?Author $author = null;

    /** @var \Tests\Tempest\Fixtures\Modules\Books\Models\Chapter[] */
    public array $chapters = [];

    #[HasOne]
    public ?Isbn $isbn = null;

    public int|string $bindingValue {
        get => $this->id->value;
    }
}
