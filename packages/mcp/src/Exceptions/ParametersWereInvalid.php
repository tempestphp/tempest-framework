<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;

final class ParametersWereInvalid extends Exception implements McpProtocolException
{
    public JsonRpcErrorCode $errorCode {
        get => JsonRpcErrorCode::INVALID_PARAMS;
    }

    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function becauseArgumentWasMissing(string $argument): self
    {
        return new self("The required argument `{$argument}` is missing.");
    }

    public static function becauseArgumentWasUnknown(string $argument): self
    {
        return new self("The argument `{$argument}` is unknown.");
    }

    public static function becauseArgumentWasNotAString(string $argument): self
    {
        return new self("The argument `{$argument}` must be a string.");
    }

    public static function becauseArgumentsWereNotAnObject(): self
    {
        return new self('The `arguments` member must be an object.');
    }

    public static function becauseValidationFailed(ArgumentsFailedValidation $exception): self
    {
        return new self($exception->getMessage());
    }
}
