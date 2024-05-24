<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class ServerError implements Response
{
    use IsResponse;

    public function __construct(string $body)
    {
        $this->status = Status::INTERNAL_SERVER_ERROR;
        $this->body = $body;
    }
}
