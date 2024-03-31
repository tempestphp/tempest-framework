<?php

declare(strict_types=1);

namespace App\Modules\Posts;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class PostStatusRequest implements Request
{
    use IsRequest;

    public int $id;

    public bool $published;
}
