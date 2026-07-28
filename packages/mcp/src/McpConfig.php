<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Mcp\Exceptions\McpServerWasAlreadyRegistered;
use Tempest\Mcp\Exceptions\McpServerWasNotFound;
use Tempest\Mcp\Exceptions\PrimitiveWasAlreadyRegistered;
use Tempest\Mcp\Exceptions\ResourceTemplateWasInvalid;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;

use function Tempest\Support\str;

final class McpConfig
{
    /** @var array<class-string, McpServerDefinition> */
    public array $servers = [];

    public function __construct(
        public bool $listResourceTemplatesAsResources = false,
    ) {}

    public function addServer(string $class, McpServer $attribute): void
    {
        $name = $attribute->name ?? str($class)->classBasename()->kebab()->toString();
        $path = $attribute->path !== null
            ? '/' . trim($attribute->path, '/')
            : null;

        foreach ($this->servers as $server) {
            if ($server->name === $name) {
                throw McpServerWasAlreadyRegistered::withName($name);
            }

            if ($path !== null && $server->path === $path) {
                throw McpServerWasAlreadyRegistered::withPath($path);
            }
        }

        $this->servers[$class] = new McpServerDefinition(
            class: $class,
            name: $name,
            version: $attribute->version ?? '0.0.1',
            instructions: $attribute->instructions,
            path: $path,
        );
    }

    public function addTool(MethodReflector $handler, McpTool $attribute, ?string $owner = null): void
    {
        $owner ??= $handler->getDeclaringClass()->getName();
        $server = $this->resolveServer($handler, $attribute->server, $owner);

        if (! $server instanceof McpServerDefinition) {
            return;
        }

        $name = $attribute->name ?? str($handler->getName())->snake()->toString();

        if (isset($server->tools[$name])) {
            throw PrimitiveWasAlreadyRegistered::tool($name, $server->name);
        }

        $server->tools[$name] = new ToolDefinition(
            name: $name,
            description: $attribute->description,
            class: $owner,
            handler: $handler,
        );
    }

    public function addPrompt(MethodReflector $handler, McpPrompt $attribute, ?string $owner = null): void
    {
        $owner ??= $handler->getDeclaringClass()->getName();
        $server = $this->resolveServer($handler, $attribute->server, $owner);

        if (! $server instanceof McpServerDefinition) {
            return;
        }

        $name = $attribute->name ?? str($handler->getName())->snake()->toString();

        if (isset($server->prompts[$name])) {
            throw PrimitiveWasAlreadyRegistered::prompt($name, $server->name);
        }

        $server->prompts[$name] = new PromptDefinition(
            name: $name,
            description: $attribute->description,
            class: $owner,
            handler: $handler,
        );
    }

    public function addResource(MethodReflector $handler, McpResource $attribute, ?string $owner = null): void
    {
        $owner ??= $handler->getDeclaringClass()->getName();
        $server = $this->resolveServer($handler, $attribute->server, $owner);

        if (! $server instanceof McpServerDefinition) {
            return;
        }

        $resource = new ResourceDefinition(
            uri: $attribute->uri,
            name: $attribute->name ?? str($handler->getName())->snake()->toString(),
            description: $attribute->description,
            mimeType: $attribute->mimeType,
            class: $owner,
            handler: $handler,
        );

        foreach ($resource->uriTemplate->variableNames as $variable) {
            if (! $handler->getParameter($variable) instanceof ParameterReflector) {
                throw new ResourceTemplateWasInvalid(
                    uri: $resource->uri,
                    variable: $variable,
                    class: $handler->getDeclaringClass()->getName(),
                    method: $handler->getName(),
                );
            }
        }

        if (isset($server->resources[$resource->uri]) || isset($server->resourceTemplates[$resource->uri])) {
            throw PrimitiveWasAlreadyRegistered::resource($resource->uri, $server->name);
        }

        if ($resource->isTemplated()) {
            $server->resourceTemplates[$resource->uri] = $resource;
        } else {
            $server->resources[$resource->uri] = $resource;
        }
    }

    public function getServerByName(string $name): ?McpServerDefinition
    {
        return array_find($this->servers, static fn ($server) => $server->name === $name);
    }

    public function getServerByPath(string $path): ?McpServerDefinition
    {
        $path = '/' . trim($path, '/');

        return array_find($this->servers, static fn ($server) => $server->path === $path);
    }

    /**
     * Resolves the server a primitive belongs to. `$owner` is the class the method was discovered on, which
     * may differ from its declaring class for inherited methods. Returns `null` when the primitive should be
     * skipped: inherited copies of explicitly attached methods, methods inherited by non-server classes, and
     * methods on abstract classes that are only exposed through concrete server subclasses.
     */
    private function resolveServer(MethodReflector $handler, ?string $serverClass, string $owner): ?McpServerDefinition
    {
        $declaringClass = $handler->getDeclaringClass()->getName();

        if ($serverClass !== null) {
            if ($declaringClass !== $owner) {
                return null;
            }

            return $this->servers[$serverClass] ?? throw McpServerWasNotFound::forPrimitive($serverClass, $declaringClass, $handler->getName());
        }

        if (isset($this->servers[$owner])) {
            return $this->servers[$owner];
        }

        if ($declaringClass !== $owner || new ClassReflector($owner)->getReflection()->isAbstract()) {
            return null;
        }

        throw McpServerWasNotFound::forUnattachedPrimitive($declaringClass, $handler->getName());
    }
}
