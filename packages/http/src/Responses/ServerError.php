<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Exception;
use Generator;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\View\View;

final class ServerError implements Response
{
    use IsResponse;

    public function __construct(
        View|Generator|string|array|null $body = null,
        private(set) ?Exception $exception = null,
    ) {
        $this->status = Status::INTERNAL_SERVER_ERROR;
        $this->body = $body;
    }
}
