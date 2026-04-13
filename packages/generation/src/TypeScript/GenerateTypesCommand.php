<?php

declare(strict_types=1);

namespace Tempest\Generation\TypeScript;

use Psr\Container\ContainerInterface;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class GenerateTypesCommand
{
    use HasConsole;

    public function __construct(
        private TypeScriptGenerationConfig $config,
        private TypeScriptGenerator $generator,
        private ContainerInterface $container,
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
