<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;

final class GenerateTypesCommand
{
    use HasConsole;

    public function __construct(
        private readonly TypeScriptGenerationConfig $config,
        private readonly TypeScriptGenerator $generator,
        private readonly Container $container,
    ) {}

    #[ConsoleCommand(
        name: 'generate:typescript-types',
        description: 'Generate TypeScript types from PHP classes.',
    )]
    public function __invoke(): void
    {
        $this->console->writeln();

        $output = $this->generator->generate();

        if ($output->isEmpty()) {
            $this->console->warning('No types found to generate.');
            return;
        }

        $writer = $this->container->get($this->config->writer);
        $writer->write($output);

        $this->console->success(sprintf(
            'Generated %d type definitions across %d namespaces.',
            count($output->getAllDefinitions()),
            count($output->getNamespaces()),
        ));
    }
}
