<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Requests;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

final class CreateBookRequest implements Request
{
    use IsRequest;

    public string $title;
}
