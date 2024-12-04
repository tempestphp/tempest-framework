<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Posts;

use Tempest\Http\Request;
use Tempest\Mapper\Strict;

// TODO: Clean this up.
#[Strict]
final class PostRequest extends Request
{
    public string $title;

    public string $text;
}
