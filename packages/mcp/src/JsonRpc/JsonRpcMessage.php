<?php

declare(strict_types=1);

namespace Tempest\Mcp\JsonRpc;

interface JsonRpcMessage
{
    public string $method { get; }

    public array $params { get; }

    public function toArray(): array;
}
