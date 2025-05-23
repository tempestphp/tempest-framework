<?php

namespace Tempest\HttpApi;

use Tempest\Http\IsRequest;
use Tempest\Http\Request;

class IndexApiRequest implements Request
{
    use IsRequest;

    public ?int $page = null;

    public ?int $perPage = null;

    public ?string $sort = null;

    public ?string $direction = null;

    public ?string $search = null;

    public null|string|int $cursor = null;
}
