<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;

final class McpServerWasNotFound extends Exception implements McpException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withName(string $name): self
    {
        return new self("There is no MCP server named `{$name}`.");
    }

    public static function withClass(string $class): self
    {
        return new self("The class `{$class}` is not annotated with `#[McpServer]`.");
    }

    public static function forPrimitive(string $server, string $class, string $method): self
    {
        return new self("`{$class}::{$method}` references the MCP server `{$server}`, but that class is not annotated with `#[McpServer]`.");
    }

    public static function forUnattachedPrimitive(string $class, string $method): self
    {
        return new self("`{$class}::{$method}` is not part of an `#[McpServer]` class, so it must specify which server it belongs to via the `server` parameter.");
    }
}
