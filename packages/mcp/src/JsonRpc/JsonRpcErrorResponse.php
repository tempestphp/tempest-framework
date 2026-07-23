<?php

declare(strict_types=1);

namespace Tempest\Mcp\JsonRpc;

use Tempest\Mcp\Exceptions\McpProtocolException;

final readonly class JsonRpcErrorResponse implements JsonRpcResponse
{
    public function __construct(
        public string|int|null $id,
        public JsonRpcErrorCode $code,
        public string $message,
        public ?array $data = null,
    ) {}

    public static function fromException(McpProtocolException $exception, string|int|null $id = null): self
    {
        return new self(
            id: $id,
            code: $exception->errorCode,
            message: $exception->getMessage(),
        );
    }

    public function toArray(): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $this->id,
            'error' => [
                'code' => $this->code->value,
                'message' => $this->message,
                ...($this->data === null ? [] : ['data' => $this->data]),
            ],
        ];
    }
}
