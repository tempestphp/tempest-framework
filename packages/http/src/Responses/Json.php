<?php

declare(strict_types=1);

namespace Tempest\Http\Responses;

use JsonSerializable;
use Tempest\Http\IsResponse;
use Tempest\Http\Response;
use Tempest\Http\Status;

final class Json implements Response
{
    use IsResponse;

    /**
     * @param JsonSerializable|array<mixed>|null $body
     * @param Status|null $status
     * @param array<string, \Tempest\Http\Header|string|string[]>|list<\Tempest\Http\Header> $headers
     */
    public function __construct(JsonSerializable|array|null $body = null, ?Status $status = null, array $headers = [])
    {
        $this->status = $status ?? Status::OK;
        $this->body = $body;

        $this->addHeader('Accept', 'application/json');
        $this->addHeader('Content-Type', 'application/json');
        $this->addHeaders($headers);
    }
}
