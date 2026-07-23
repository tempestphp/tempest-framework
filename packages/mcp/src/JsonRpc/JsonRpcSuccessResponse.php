<?php

declare(strict_types=1);

namespace Tempest\Mcp\JsonRpc;

use stdClass;

final readonly class JsonRpcSuccessResponse implements JsonRpcResponse
{
    public function __construct(
        public string|int $id,
        public array $result,
    ) {}

    public function toArray(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'result' => $this->result === [] ? new stdClass() : $this->result,
        ];
    }
}
