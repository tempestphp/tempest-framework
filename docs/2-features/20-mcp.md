---
title: MCP
description: "Tempest's MCP component lets your application expose tools, prompts and resources to AI clients over the Model Context Protocol."
keywords: "Experimental"
experimental: true
---

:::warning
Tempest's MCP component is currently experimental and is not covered by our backwards compatibility promise.
:::

## Overview

The [Model Context Protocol](https://modelcontextprotocol.io) is an open standard that lets AI clients, such as Claude or Cursor, interact with your application through tools, prompts and resources.

Tempest ships with an MCP server framework in `tempest/mcp`. Servers are plain classes annotated with {b`Tempest\Mcp\McpServer`}, and their capabilities are methods annotated with {b`Tempest\Mcp\McpTool`}, {b`Tempest\Mcp\McpPrompt`} or {b`Tempest\Mcp\McpResource`}. Both are registered through [discovery](../1-essentials/05-discovery.md).

## Defining a server

An MCP server is a class with the {b`Tempest\Mcp\McpServer`} attribute. Its name is derived from the class name and its version defaults to `0.0.1`, unless specified in the attribute. When a `path` is provided, the server is exposed over HTTP; every server is also accessible through the `mcp:serve` command.

```php app/DemoServer.php
use Tempest\Mcp\McpServer;
use Tempest\Mcp\McpTool;

#[McpServer(path: '/mcp')]
final class DemoServer
{
    #[McpTool(description: 'Adds two numbers')]
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
```

Tool, prompt and resource methods defined inside an `#[McpServer]` class are automatically attached to that server. This includes public methods inherited from parent classes, so servers may share primitives through a common base class, which may be abstract. Methods in other classes may attach themselves to a server by specifying the `server` parameter:

```php app/OrderTools.php
use Tempest\Mcp\McpTool;

final readonly class OrderTools
{
    #[McpTool(server: DemoServer::class)]
    public function countOrders(): int
    {
        // …
    }
}
```

Handler classes and their methods are resolved through the [container](../1-essentials/05-container.md), so dependencies may be injected in the constructor or directly as method parameters.

## Tools

Tools are methods annotated with {b`Tempest\Mcp\McpTool`}. The tool name defaults to the snake-cased method name, and the input schema is derived from the method signature.

Scalar parameters, backed enums and arrays become schema properties; parameters with default values or nullable types become optional; everything else is injected by the container and excluded from the schema.

```php app/DemoServer.php
use Tempest\Mcp\Description;
use Tempest\Mcp\McpTool;
use Tempest\Validation\Rules\IsBetween;

#[McpTool(description: 'Rates a product')]
public function rate(
    ProductRepository $products, // injected, not part of the schema
    #[Description('The identifier of the product')]
    string $product,
    #[IsBetween(min: 1, max: 5)]
    int $rating,
): string {
    // …
}
```

Validation attributes from `tempest/validation` double as schema constraints and are enforced at call time. For instance, `#[IsBetween]` becomes `minimum` and `maximum`, `#[HasLength]` becomes `minLength` and `maxLength`, and `#[IsIn]` becomes an `enum`. When validation fails, the client receives an error result containing the validation messages.

Return values are normalized to MCP content:

- A `string` or other scalar becomes text content;
- An associative array or object becomes JSON text alongside `structuredContent`;
- Instances of {b`Tempest\Mcp\Content\Text`}, {b`Tempest\Mcp\Content\Image`}, {b`Tempest\Mcp\Content\Audio`} and {b`Tempest\Mcp\Content\ResourceLink`}, or lists of them, are passed through as-is.

Exceptions thrown by a tool handler are reported to the [exception handler](./14-exception-handling.md) and returned to the client as an error result.

## Prompts

Prompts are methods annotated with {b`Tempest\Mcp\McpPrompt`}. Their parameters are advertised as prompt arguments, and the return value becomes the prompt messages.

```php app/DemoServer.php
use Tempest\Mcp\McpPrompt;

#[McpPrompt(description: 'Generates a code review prompt')]
public function reviewCode(string $code): string
{
    return "Review the following code: {$code}";
}
```

## Resources

Resources are methods annotated with {b`Tempest\Mcp\McpResource`}, which requires a `uri`. A URI may contain template variables that correspond to method parameters, in which case the resource is advertised as a resource template and matched dynamically when read.

```php app/DemoServer.php
use Tempest\Mcp\Content\Blob;
use Tempest\Mcp\McpResource;

#[McpResource(uri: 'app://config', mimeType: 'application/json')]
public function config(): array
{
    return ['env' => 'production'];
}

#[McpResource(uri: 'app://users/{id}')]
public function user(int $id): string
{
    return "User #{$id}";
}
```

String return values become text resource contents, while {b`Tempest\Mcp\Content\Blob`} instances become binary blob contents.

Some MCP clients only request the standard resource list and do not request resource templates. To also include resource templates in `resources/list`, create an `mcp.config.php` file:

```php app/mcp.config.php
use Tempest\Mcp\McpConfig;

return new McpConfig(
    listResourceTemplatesAsResources: true,
);
```

Resource templates will remain available through `resources/templates/list`.

## Transports

### HTTP

When {b`Tempest\Mcp\McpServer`} specifies a `path`, a `POST` route is registered automatically. The HTTP transport is stateless: each request receives a single JSON response, notifications are acknowledged with `202 Accepted`, and other HTTP methods are answered with `405 Method Not Allowed`. You may protect the route like any other by adding middleware through a [route decorator](../1-essentials/01-routing.md), or by placing the server behind your existing authentication middleware.

### Standard input/output

Every discovered server can be served over standard input and output, which is the transport most local MCP clients use:

```sh
{:hl-comment:# Serve a server by its name:} 
./tempest mcp:serve demo-server
```

Use `mcp:list` to see all discovered servers, their transports, and how many tools, prompts and resources they expose.

## Testing

The framework provides a `mcp` property in tests that extend `IntegrationTest`. It drives the protocol in-process and performs the initialization handshake.

```php tests/DemoServerTest.php
public function test_add(): void
{
    $this->mcp
        ->onServer(DemoServer::class)
        ->callTool('add', ['a' => 1, 'b' => 2])
        ->assertOk()
        ->assertText('3');
}
```

The connection object provides `callTool()`, `getPrompt()`, `readResource()`, `listTools()`, `listResources()`, `listPrompts()`, and a generic `send()` method for raw protocol requests. Responses may be asserted with `assertOk()`, `assertError()`, `assertText()`, `assertTextContains()`, `assertStructured()`, `assertToolListed()` and `assertSee()`.
