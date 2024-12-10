<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Posts;

use Tempest\Mapper\Strict;
use Tempest\Router\IsRequest;
use Tempest\Router\Request;

#[Strict]
final class PostRequest implements Request
{
    use IsRequest;

    public string $title;

    public string $text;
}
