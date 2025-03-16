<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Core\Kernel;

final readonly class EditorMetadataCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private Kernel $kernel,
    ) {
    }

    #[ConsoleCommand(
        name: 'editor:metadata',
        description: 'Returns metadata for integration with text editors.',
        hidden: true,
    )]
    public function __invoke(): void
    {
        $this->console->write(
            contents: json_encode($this->collectMetadata(), JSON_PRETTY_PRINT),
        );
    }

    /**
     * Collects metadata about the framework and the application
     */
    private function collectMetadata(): array
    {
        return ['framework' => $this->getFrameworkMetadata()];
    }

    /**
     * Retrieves the metadata of the framework.
     *
     * @return array<string, mixed>
     */
    private function getFrameworkMetadata(): array
    {
        return [
            'name' => 'Tempest',
            'version' => $this->kernel::VERSION,
        ];
    }
}
