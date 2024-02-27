<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class CreatedResponse implements Response
{
    use IsResponse;

    public function __construct(
        private string $body = '',
        private array $headers = [],
    ) {
        $this->status = Status::CREATED;
    }
}
