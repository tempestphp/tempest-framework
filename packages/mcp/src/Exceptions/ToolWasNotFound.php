<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

final class ToolWasNotFound extends Exception implements McpProtocolException
{
    public JsonRpcErrorCode $errorCode {
        get => JsonRpcErrorCode::INVALID_PARAMS;
    }

    public function __construct(
        public readonly string $tool,
    ) {
        parent::__construct("The tool `{$tool}` does not exist.");
    }
}
