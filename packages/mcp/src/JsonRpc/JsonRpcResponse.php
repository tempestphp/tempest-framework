<?php

declare(strict_types=1);

namespace Tempest\Mcp\JsonRpc;

interface JsonRpcResponse
{
    public function toArray(): array;
}
