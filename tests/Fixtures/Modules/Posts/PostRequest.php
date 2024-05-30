<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Posts;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class PostRequest implements Request
{
    use IsRequest;

    public string $title;

    public string $text;
}
