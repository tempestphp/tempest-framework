<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

final class MethodWasNotFound extends Exception implements McpProtocolException
{
    public JsonRpcErrorCode $errorCode {
        get => JsonRpcErrorCode::METHOD_NOT_FOUND;
    }

    public function __construct(
        public readonly string $method,
    ) {
        parent::__construct("The method `{$method}` is not supported.");
    }
}
