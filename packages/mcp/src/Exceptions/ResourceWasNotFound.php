<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

final class ResourceWasNotFound extends Exception implements McpProtocolException
{
    public JsonRpcErrorCode $errorCode {
        get => JsonRpcErrorCode::RESOURCE_NOT_FOUND;
    }

    public function __construct(
        public readonly string $uri,
    ) {
        parent::__construct("The resource `{$uri}` does not exist.");
    }
}
