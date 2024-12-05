<?php

declare(strict_types=1);

namespace Tempest\Router\Responses;

use Tempest\Http\Status;
use Tempest\Router\IsResponse;
use Tempest\Router\Response;

final class Forbidden implements Response
{
    use IsResponse;

    public function __construct()
    {
        $this->status = Status::FORBIDDEN;
    }
}
