<?php

declare(strict_types=1);

namespace Tempest\Mcp\JsonRpc;

use Tempest\Mcp\Exceptions\RequestWasInvalid;

final readonly class JsonRpcNotification implements JsonRpcMessage
{
    public function __construct(
        public string $method,
        public array $params = [],
    ) {}

    public static function fromArray(array $raw): self
    {
        if (($raw['jsonrpc'] ?? null) !== '2.0') {
            throw RequestWasInvalid::becauseJsonRpcVersionWasMissing();
        }

        if (! is_string($raw['method'] ?? null)) {
            throw RequestWasInvalid::becauseMethodWasMissing();
        }

        $params = $raw['params'] ?? [];

        if (! is_array($params) || $params !== [] && array_is_list($params)) {
            throw RequestWasInvalid::becauseParamsWereInvalid();
        }

        return new self(
            method: $raw['method'],
            params: $params,
        );
    }

    public function toArray(): array
    {
        return [
            'jsonrpc' => '2.0',
            'method' => $this->method,
            ...($this->params === [] ? [] : ['params' => $this->params]),
        ];
    }
}
