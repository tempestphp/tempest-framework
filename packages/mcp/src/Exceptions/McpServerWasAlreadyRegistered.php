<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;

final class McpServerWasAlreadyRegistered extends Exception implements McpException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function withName(string $name): self
    {
        return new self("An MCP server named `{$name}` is already registered.");
    }

    public static function withPath(string $path): self
    {
        return new self("An MCP server is already registered on the path `{$path}`.");
    }
}
