<?php

declare(strict_types=1);

namespace Tempest\Mcp;

use Tempest\Mcp\JsonRpc\JsonRpcResponse;

use function Tempest\Support\Json\encode;

final readonly class StdioTransport
{
    public function __construct(
        private McpRequestHandler $requestHandler,
    ) {}

    /**
     * Runs a newline-delimited JSON-RPC loop for the given server, reading messages from `$input` and writing responses to `$output` until the input reaches its end.
     *
     * @param resource $input
     * @param resource $output
     */
    public function run(McpServerDefinition $server, mixed $input, mixed $output): void
    {
        while (($line = fgets($input)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $response = $this->requestHandler->handle($server, $line);

            if ($response instanceof JsonRpcResponse) {
                fwrite($output, encode($response->toArray()) . PHP_EOL);
            }
        }
    }
}
