<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

interface McpProtocolException extends McpException
{
    public JsonRpcErrorCode $errorCode { get; }

    public function getMessage(): string;
}
