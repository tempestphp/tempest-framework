<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use JsonException;
use stdClass;
use Tempest\Container\Container;
use Tempest\Container\Tag;
use Tempest\Core\Exceptions\ExceptionProcessor;
use Tempest\Mcp\Content\Content;
use Tempest\Mcp\Content\Text;
use Tempest\Mcp\Exceptions\ArgumentsFailedValidation;
use Tempest\Mcp\Exceptions\JsonCouldNotBeParsed;
use Tempest\Mcp\Exceptions\McpProtocolException;
use Tempest\Mcp\Exceptions\MethodWasNotFound;
use Tempest\Mcp\Exceptions\ParametersWereInvalid;
use Tempest\Mcp\Exceptions\PromptWasNotFound;
use Tempest\Mcp\Exceptions\RequestWasInvalid;
use Tempest\Mcp\Exceptions\ResourceWasNotFound;
use Tempest\Mcp\Exceptions\ToolWasNotFound;
use Tempest\Mcp\JsonRpc\JsonRpcErrorCode;
use Tempest\Mcp\JsonRpc\JsonRpcErrorResponse;
use Tempest\Mcp\JsonRpc\JsonRpcNotification;
use Tempest\Mcp\JsonRpc\JsonRpcRequest;
use Tempest\Mcp\JsonRpc\JsonRpcResponse;
use Tempest\Mcp\JsonRpc\JsonRpcSuccessResponse;
use Tempest\Reflection\MethodReflector;
use Throwable;

use function Tempest\Support\Json\encode;

final readonly class McpRequestHandler
{
    public function __construct(
        private Container $container,
        private McpConfig $config,
        private SchemaGenerator $schemaGenerator,
        private ArgumentBinder $argumentBinder,
        private ExceptionProcessor $exceptionProcessor,
    ) {}

    /**
     * Handles a single raw JSON-RPC message for the given server. Returns `null` for notifications, which expect no response.
     */
    public function handle(McpServerDefinition $server, string $rawMessage): ?JsonRpcResponse
    {
        try {
            $decoded = json_decode($rawMessage, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return JsonRpcErrorResponse::fromException(new JsonCouldNotBeParsed());
        }

        if (! is_array($decoded) || $decoded === []) {
            return JsonRpcErrorResponse::fromException(RequestWasInvalid::becauseMethodWasMissing());
        }

        if (array_is_list($decoded)) {
            return JsonRpcErrorResponse::fromException(RequestWasInvalid::becauseBatchesAreNotSupported());
        }

        if (! array_key_exists('id', $decoded)) {
            return $this->handleNotification($decoded);
        }

        $id = is_string($decoded['id']) || is_int($decoded['id']) ? $decoded['id'] : null;

        try {
            $request = JsonRpcRequest::fromArray($decoded);
        } catch (McpProtocolException $exception) {
            return JsonRpcErrorResponse::fromException($exception, $id);
        }

        try {
            $result = match ($request->method) {
                'initialize' => $this->initialize($server, $request),
                'ping' => [],
                'tools/list' => $this->listTools($server),
                'tools/call' => $this->callTool($server, $request),
                'resources/list' => $this->listResources($server),
                'resources/templates/list' => $this->listResourceTemplates($server),
                'resources/read' => $this->readResource($server, $request),
                'prompts/list' => $this->listPrompts($server),
                'prompts/get' => $this->getPrompt($server, $request),
                default => throw new MethodWasNotFound($request->method),
            };

            return new JsonRpcSuccessResponse($request->id, $result);
        } catch (McpProtocolException $exception) {
            return JsonRpcErrorResponse::fromException($exception, $request->id);
        } catch (Throwable $throwable) {
            $this->exceptionProcessor->process($throwable);

            return new JsonRpcErrorResponse(
                id: $request->id,
                code: JsonRpcErrorCode::INTERNAL_ERROR,
                message: 'An internal error occurred while processing the request.',
            );
        }
    }

    private function handleNotification(array $decoded): ?JsonRpcErrorResponse
    {
        try {
            JsonRpcNotification::fromArray($decoded);
        } catch (McpProtocolException $exception) {
            return JsonRpcErrorResponse::fromException($exception);
        }

        return null;
    }

    private function initialize(McpServerDefinition $server, JsonRpcRequest $request): array
    {
        $requestedVersion = $request->params['protocolVersion'] ?? null;
        $version = ProtocolVersion::negotiate(is_string($requestedVersion) ? $requestedVersion : null);

        $capabilities = [];

        if ($server->tools !== []) {
            $capabilities['tools'] = ['listChanged' => false];
        }

        if ($server->resources !== [] || $server->resourceTemplates !== []) {
            $capabilities['resources'] = ['listChanged' => false];
        }

        if ($server->prompts !== []) {
            $capabilities['prompts'] = ['listChanged' => false];
        }

        return [
            'protocolVersion' => $version->value,
            'capabilities' => $capabilities === [] ? new stdClass() : $capabilities,
            'serverInfo' => [
                'name' => $server->name,
                'version' => $server->version,
            ],
            ...($server->instructions === null ? [] : ['instructions' => $server->instructions]),
        ];
    }

    private function listTools(McpServerDefinition $server): array
    {
        $tools = [];

        foreach ($server->tools as $tool) {
            $tools[] = [
                'name' => $tool->name,
                ...($tool->description === null ? [] : ['description' => $tool->description]),
                'inputSchema' => $this->schemaGenerator->createInputSchema($tool->handler),
            ];
        }

        return ['tools' => $tools];
    }

    private function callTool(McpServerDefinition $server, JsonRpcRequest $request): array
    {
        $name = $this->resolveStringParam($request, 'name');
        $tool = $server->tools[$name] ?? throw new ToolWasNotFound($name);
        $arguments = $this->resolveArguments($request);

        try {
            $boundArguments = $this->argumentBinder->bind($tool->handler, $arguments);

            return $this->createToolResult($this->invoke($tool->class, $tool->handler, $boundArguments));
        } catch (ArgumentsFailedValidation $exception) {
            return $this->createToolError($exception);
        } catch (McpProtocolException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            $this->exceptionProcessor->process($throwable);

            return $this->createToolError($throwable);
        }
    }

    private function listResources(McpServerDefinition $server): array
    {
        $resources = [];

        foreach ($server->resources as $resource) {
            $resources[] = [
                'uri' => $resource->uri,
                'name' => $resource->name,
                ...($resource->description === null ? [] : ['description' => $resource->description]),
                ...($resource->mimeType === null ? [] : ['mimeType' => $resource->mimeType]),
            ];
        }

        if ($this->config->listResourceTemplatesAsResources) {
            foreach ($server->resourceTemplates as $resource) {
                $resources[] = [
                    'uri' => $resource->uri,
                    'name' => $resource->name,
                    ...($resource->description === null ? [] : ['description' => $resource->description]),
                    ...($resource->mimeType === null ? [] : ['mimeType' => $resource->mimeType]),
                ];
            }
        }
        return ['resources' => $resources];
    }

    private function listResourceTemplates(McpServerDefinition $server): array
    {
        $resourceTemplates = [];

        foreach ($server->resourceTemplates as $resource) {
            $resourceTemplates[] = [
                'uriTemplate' => $resource->uri,
                'name' => $resource->name,
                ...($resource->description === null ? [] : ['description' => $resource->description]),
                ...($resource->mimeType === null ? [] : ['mimeType' => $resource->mimeType]),
            ];
        }

        return ['resourceTemplates' => $resourceTemplates];
    }

    private function readResource(McpServerDefinition $server, JsonRpcRequest $request): array
    {
        $uri = $this->resolveStringParam($request, 'uri');

        [$resource, $variables] = $this->resolveResource($server, $uri);

        try {
            $boundArguments = $this->argumentBinder->bind($resource->handler, $variables);
        } catch (ArgumentsFailedValidation $exception) {
            throw ParametersWereInvalid::becauseValidationFailed($exception);
        }

        $result = $this->invoke($resource->class, $resource->handler, $boundArguments);

        return ['contents' => $this->createResourceContents($resource, $uri, $result)];
    }

    private function listPrompts(McpServerDefinition $server): array
    {
        $prompts = [];

        foreach ($server->prompts as $prompt) {
            $arguments = $this->schemaGenerator->createPromptArguments($prompt->handler);

            $prompts[] = [
                'name' => $prompt->name,
                ...($prompt->description === null ? [] : ['description' => $prompt->description]),
                ...($arguments === [] ? [] : ['arguments' => $arguments]),
            ];
        }

        return ['prompts' => $prompts];
    }

    private function getPrompt(McpServerDefinition $server, JsonRpcRequest $request): array
    {
        $name = $this->resolveStringParam($request, 'name');
        $prompt = $server->prompts[$name] ?? throw new PromptWasNotFound($name);
        $arguments = $this->resolveArguments($request);

        try {
            $boundArguments = $this->argumentBinder->bind($prompt->handler, $arguments);
        } catch (ArgumentsFailedValidation $exception) {
            throw ParametersWereInvalid::becauseValidationFailed($exception);
        }

        $result = $this->invoke($prompt->class, $prompt->handler, $boundArguments);

        return [
            ...($prompt->description === null ? [] : ['description' => $prompt->description]),
            'messages' => $this->createPromptMessages($result),
        ];
    }

    private function invoke(string $class, MethodReflector $handler, array $boundArguments): mixed
    {
        $arguments = [];

        foreach ($handler->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $boundArguments)) {
                $arguments[$name] = $boundArguments[$name];

                continue;
            }

            if ($parameter->hasDefaultValue()) {
                continue;
            }

            $arguments[$name] = $this->container->get(
                $parameter->getType()->getName(),
                $parameter->getAttribute(Tag::class)?->name,
            );
        }

        return $handler->invokeArgs(
            $this->container->get($class),
            $arguments,
        );
    }

    private function resolveStringParam(JsonRpcRequest $request, string $param): string
    {
        $value = $request->params[$param] ?? null;

        if ($value === null) {
            throw ParametersWereInvalid::becauseArgumentWasMissing($param);
        }

        if (! is_string($value)) {
            throw ParametersWereInvalid::becauseArgumentWasNotAString($param);
        }

        return $value;
    }

    private function resolveArguments(JsonRpcRequest $request): array
    {
        $arguments = $request->params['arguments'] ?? [];

        if (! is_array($arguments) || $arguments !== [] && array_is_list($arguments)) {
            throw ParametersWereInvalid::becauseArgumentsWereNotAnObject();
        }

        return $arguments;
    }

    /**
     * @return array{ResourceDefinition, array<string, string>}
     */
    private function resolveResource(McpServerDefinition $server, string $uri): array
    {
        if (isset($server->resources[$uri])) {
            return [$server->resources[$uri], []];
        }

        foreach ($server->resourceTemplates as $resource) {
            if (($variables = $resource->uriTemplate->match($uri)) !== null) {
                return [$resource, $variables];
            }
        }

        throw new ResourceWasNotFound($uri);
    }

    private function createToolResult(mixed $result): array
    {
        [$content, $structuredContent] = $this->normalizeContent($result);

        return [
            'content' => $content,
            'isError' => false,
            ...($structuredContent === null ? [] : ['structuredContent' => $structuredContent]),
        ];
    }

    private function createToolError(Throwable $throwable): array
    {
        return [
            'content' => [new Text($throwable->getMessage())->toContent()],
            'isError' => true,
        ];
    }

    /**
     * @return array{array[], mixed}
     */
    private function normalizeContent(mixed $result): array
    {
        if ($result === null) {
            return [[], null];
        }

        if ($result instanceof Content) {
            return [[$result->toContent()], null];
        }

        if (is_array($result) && $result !== [] && array_is_list($result) && array_all($result, static fn (mixed $item) => $item instanceof Content)) {
            return [array_map(static fn (Content $item) => $item->toContent(), $result), null];
        }

        if (is_string($result)) {
            return [[new Text($result)->toContent()], null];
        }

        if (is_scalar($result)) {
            return [[new Text(encode($result))->toContent()], null];
        }

        $structuredContent = is_array($result) && ! array_is_list($result) || is_object($result)
            ? $result
            : null;

        return [[new Text(encode($result))->toContent()], $structuredContent];
    }

    private function createResourceContents(ResourceDefinition $resource, string $uri, mixed $result): array
    {
        if ($result instanceof Content) {
            return [$result->toResourceContents($uri, $resource->mimeType)];
        }

        if (is_array($result) && $result !== [] && array_is_list($result) && array_all($result, static fn (mixed $item) => $item instanceof Content)) {
            return array_map(static fn (Content $item) => $item->toResourceContents($uri, $resource->mimeType), $result);
        }

        if (is_string($result)) {
            return [new Text($result)->toResourceContents($uri, $resource->mimeType)];
        }

        return [
            [
                'uri' => $uri,
                'mimeType' => $resource->mimeType ?? 'application/json',
                'text' => encode($result),
            ],
        ];
    }

    private function createPromptMessages(mixed $result): array
    {
        $contents = match (true) {
            $result instanceof Content => [$result->toContent()],
            is_array($result) && $result !== [] && array_is_list($result) && array_all($result, static fn (mixed $item) => $item instanceof Content) => array_map(
                static fn (Content $item) => $item->toContent(),
                $result,
            ),
            is_string($result) => [new Text($result)->toContent()],
            default => [new Text(encode($result))->toContent()],
        };

        return array_map(static fn (array $content) => [
            'role' => 'user',
            'content' => $content,
        ], $contents);
    }
}
