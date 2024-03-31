<?php

declare(strict_types=1);

namespace App\Modules\Posts;

use App\ValueObjects\ModelId;
use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class PostStatusRequest implements Request
{
    use IsRequest;

    public ModelId $id;

    public bool $published;
}
