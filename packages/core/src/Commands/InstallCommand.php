<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Console\Actions\PromptForConsoleInput;
use Tempest\Console\Actions\ResolveConsoleInput;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use Tempest\Core\InstallerArgument;
use Tempest\Core\InstallerConfig;
use Tempest\Core\InstallerDefinition;
use Tempest\Intl;
use Tempest\Support\Arr;
use Tempest\Support\Str;

if (class_exists(ConsoleCommand::class)) {
    final readonly class InstallCommand
    {
        use HasConsole;

        public function __construct(
            private InstallerConfig $installerConfig,
            private Container $container,
            private ConsoleArgumentBag $consoleArgumentBag,
            private ResolveConsoleInput $resolveConsoleInput,
            private PromptForConsoleInput $promptForConsoleInput,
        ) {}

        #[ConsoleCommand(
            name: 'install',
            description: 'Installs and configures the specified feature',
            middleware: [ForceMiddleware::class],
            allowDynamicArguments: true,
        )]
        public function __invoke(
            #[ConsoleArgument(description: 'The installer to apply. If not provided, you will be prompted to select one.')]
            ?string $installer = null,
        ): void {
            $installer = $this->resolveInstaller($installer);

            if (! $installer instanceof InstallerDefinition) {
                $this->console->writeln();
                $this->console->error('The provided installer was not found.');

                return;
            }

            $instance = $this->container->get($installer->handler->getDeclaringClass()->getName());
            $arguments = $this->resolveInstallerArguments($installer);

            if (! is_array($arguments)) {
                return;
            }

            $installer->handler->invokeArgs($instance, $arguments);
        }

        private function resolveInstaller(?string $search): ?InstallerDefinition
        {
            $search ??= $this->ask(
                question: 'Choose an installer',
                options: Arr\map_with_keys(
                    array: $this->installerConfig->installers,
                    map: fn (InstallerDefinition $installer) => yield $installer->id => $installer->name,
                ),
            );

            if (! is_string($search)) {
                return null;
            }

            return Arr\first(
                array: $this->installerConfig->installers,
                filter: fn (InstallerDefinition $installer) => in_array($search, $installer->aliases, strict: true) || $installer->id === $search,
            );
        }

        private function resolveInstallerArguments(InstallerDefinition $installer): ?array
        {
            $installerArgumentBag = $this->buildInstallerArgumentBag();
            $argumentDefinitions = $this->buildInstallerArgumentDefinitions($installer);

            while (true) {
                [$validArguments, $invalidArguments] = ($this->resolveConsoleInput)(
                    argumentBag: $installerArgumentBag,
                    argumentDefinitions: $argumentDefinitions,
                );

                if ($invalidArguments === []) {
                    break;
                }

                if (! $this->console->supportsPrompting()) {
                    $missingArguments = implode(', ', array_map(
                        callback: fn (ConsoleArgumentDefinition $argumentDefinition) => $argumentDefinition->name,
                        array: $invalidArguments,
                    ));

                    $this->console->writeln();
                    $this->console->error(sprintf("Missing %s: {$missingArguments}", Intl\pluralize('argument', $invalidArguments)));

                    return null;
                }

                foreach ($invalidArguments as $argumentDefinition) {
                    ($this->promptForConsoleInput)(
                        argumentBag: $installerArgumentBag,
                        argumentDefinition: $argumentDefinition,
                    );
                }
            }

            return array_map(
                callback: fn (ConsoleInputArgument $argument) => $argument->value,
                array: $validArguments,
            );
        }

        private function buildInstallerArgumentBag(): ConsoleArgumentBag
        {
            $installerArgumentBag = new ConsoleArgumentBag([
                $this->consoleArgumentBag->getCliName(),
                'install',
            ]);

            $position = 0;

            foreach ($this->consoleArgumentBag->all() as $argument) {
                if ($argument->isPositional && $position === 0) {
                    continue;
                }

                $installerArgumentBag->add(new ConsoleInputArgument(
                    name: $argument->name,
                    position: $argument->isPositional ? $position++ : null,
                    value: $argument->value,
                    isPositional: $argument->isPositional,
                ));
            }

            return $installerArgumentBag;
        }

        /** @return list<ConsoleArgumentDefinition> */
        private function buildInstallerArgumentDefinitions(InstallerDefinition $installer): array
        {
            $argumentDefinitions = [];

            foreach ($installer->handler->getParameters() as $parameter) {
                $installerArgument = $parameter->getAttribute(InstallerArgument::class);
                $name = $installerArgument->name ?? $parameter->getName();

                $argumentDefinitions[] = new ConsoleArgumentDefinition(
                    name: $name,
                    type: $parameter->getType()->getName(),
                    default: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                    hasDefault: $parameter->isDefaultValueAvailable(),
                    position: $parameter->getPosition(),
                    isVariadic: $parameter->isVariadic(),
                    prompt: $installerArgument->prompt ?? Str\to_sentence_case($name),
                );
            }

            return $argumentDefinitions;
        }
    }
}
