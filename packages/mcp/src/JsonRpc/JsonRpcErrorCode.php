<?php

declare(strict_types=1);

namespace Tempest\Mcp\JsonRpc;

enum JsonRpcErrorCode: int
{
    case PARSE_ERROR = -32_700;
    case INVALID_REQUEST = -32_600;
    case METHOD_NOT_FOUND = -32_601;
    case INVALID_PARAMS = -32_602;
    case INTERNAL_ERROR = -32_603;
    case RESOURCE_NOT_FOUND = -32_002;
}
