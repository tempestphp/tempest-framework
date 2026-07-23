<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Status;
use Tempest\Mcp\JsonRpc\JsonRpcResponse;

use function Tempest\Support\Json\encode;

final readonly class McpHttpController
{
    public function __construct(
        private McpConfig $config,
        private McpRequestHandler $requestHandler,
    ) {}

    public function __invoke(Request $request): Response
    {
        $server = $this->config->getServerByPath($request->path);

        if (! $server instanceof McpServerDefinition) {
            return new NotFound();
        }

        if ($request->method !== Method::POST) {
            return new GenericResponse(Status::METHOD_NOT_ALLOWED);
        }

        $response = $this->requestHandler->handle($server, $request->raw ?? encode($request->body));

        if (! $response instanceof JsonRpcResponse) {
            return new GenericResponse(Status::ACCEPTED);
        }

        return new Json($response->toArray());
    }
}
