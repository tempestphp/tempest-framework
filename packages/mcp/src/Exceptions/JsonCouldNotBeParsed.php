<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

final class JsonCouldNotBeParsed extends Exception implements McpProtocolException
{
    public JsonRpcErrorCode $errorCode {
        get => JsonRpcErrorCode::PARSE_ERROR;
    }

    public function __construct()
    {
        parent::__construct('The message could not be parsed as JSON.');
    }
}
