<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class Redirect implements Response
{
    use IsResponse;

    public function __construct(
        string $to,
        string|array|null $body = null,
    ) {
        $this->status = Status::FOUND;
        $this->body = $body;
        $this->addHeader('Location', $to);
    }
}
