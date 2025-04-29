<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class Forbidden implements Response
{
    use IsResponse;

    public function __construct()
    {
        $this->status = Status::FORBIDDEN;
    }
}
