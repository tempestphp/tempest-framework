<?php

declare(strict_types=1);

namespace Tempest\Mcp\Exceptions;

use Exception;

final class PrimitiveWasAlreadyRegistered extends Exception implements McpException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function tool(string $name, string $server): self
    {
        return new self("A tool named `{$name}` is already registered on the MCP server `{$server}`.");
    }

    public static function prompt(string $name, string $server): self
    {
        return new self("A prompt named `{$name}` is already registered on the MCP server `{$server}`.");
    }

    public static function resource(string $uri, string $server): self
    {
        return new self("A resource with the URI `{$uri}` is already registered on the MCP server `{$server}`.");
    }
}
