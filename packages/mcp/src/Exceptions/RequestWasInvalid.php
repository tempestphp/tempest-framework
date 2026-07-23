<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

final class RequestWasInvalid extends Exception implements McpProtocolException
{
    public JsonRpcErrorCode $errorCode {
        get => JsonRpcErrorCode::INVALID_REQUEST;
    }

    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function becauseJsonRpcVersionWasMissing(): self
    {
        return new self('The message is not a valid JSON-RPC 2.0 message, the `jsonrpc` member must be `"2.0"`.');
    }

    public static function becauseMethodWasMissing(): self
    {
        return new self('The message is not a valid JSON-RPC 2.0 request, the `method` member must be a string.');
    }

    public static function becauseIdWasInvalid(): self
    {
        return new self('The message is not a valid JSON-RPC 2.0 request, the `id` member must be a string or an integer.');
    }

    public static function becauseParamsWereInvalid(): self
    {
        return new self('The message is not a valid JSON-RPC 2.0 request, the `params` member must be an object.');
    }

    public static function becauseBatchesAreNotSupported(): self
    {
        return new self('Batched JSON-RPC messages are not supported.');
    }
}
