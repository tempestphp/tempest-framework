<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;
use Tempest\Reflection\MethodReflector;

final class ParameterWasNotSupported extends Exception implements McpException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function becauseItWasVariadic(MethodReflector $handler, string $parameter): self
    {
        return new self(sprintf(
            'The parameter `%s` of `%s::%s` is variadic, which is not supported for MCP handlers.',
            $parameter,
            $handler->getDeclaringClass()->getName(),
            $handler->getName(),
        ));
    }

    public static function becauseUnionTypesAreNotSupported(MethodReflector $handler, string $parameter): self
    {
        return new self(sprintf(
            'The parameter `%s` of `%s::%s` has a union type, which cannot be represented in a schema.',
            $parameter,
            $handler->getDeclaringClass()->getName(),
            $handler->getName(),
        ));
    }
}
