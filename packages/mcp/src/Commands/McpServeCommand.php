<?php

declare(strict_types=1);

namespace Tempest\Mcp\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Mcp\McpConfig;
use Tempest\Mcp\McpServerDefinition;
use Tempest\Mcp\StdioTransport;

final readonly class McpServeCommand
{
    public function __construct(
        private McpConfig $config,
        private StdioTransport $transport,
        private Console $console,
    ) {}

    #[ConsoleCommand(name: 'mcp:serve', description: 'Serves an MCP server over standard input and output')]
    public function __invoke(
        #[ConsoleArgument(description: 'The name of the MCP server to serve')]
        string $server,
    ): ExitCode {
        $definition = $this->config->getServerByName($server);

        if (! $definition instanceof McpServerDefinition) {
            $this->console->error("There is no MCP server named `{$server}`.");

            return ExitCode::INVALID;
        }

        $this->transport->run($definition, STDIN, STDOUT);

        return ExitCode::SUCCESS;
    }
}
