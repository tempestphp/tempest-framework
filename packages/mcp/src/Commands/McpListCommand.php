<?php

declare(strict_types=1);

namespace Tempest\Mcp\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Mcp\McpConfig;

final readonly class McpListCommand
{
    use HasConsole;

    public function __construct(
        private McpConfig $config,
    ) {}

    #[ConsoleCommand(name: 'mcp:list', description: 'Lists all discovered MCP servers')]
    public function __invoke(): void
    {
        if ($this->config->servers === []) {
            $this->console->info('There are no MCP servers.');

            return;
        }

        $this->console->header('MCP servers');

        foreach ($this->config->servers as $server) {
            $transports = $server->path !== null
                ? "stdio, http ({$server->path})"
                : 'stdio';

            $counts = sprintf(
                '%d tools, %d prompts, %d resources',
                count($server->tools),
                count($server->prompts),
                count($server->resources) + count($server->resourceTemplates),
            );

            $this->console->keyValue($server->name, "{$transports} — {$counts}");
        }
    }
}
