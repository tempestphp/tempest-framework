<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use Tempest\Core\Installer;
use Tempest\Core\InstallerConfig;
use function Tempest\Support\arr;

final readonly class InstallCommand
{
    use HasConsole;

    public function __construct(
        private InstallerConfig $installerConfig,
        private Console $console,
        private Container $container,
    ) {
    }

    #[ConsoleCommand(name: 'install', description: 'Applies the specified installer', middleware: [ForceMiddleware::class])]
    public function __invoke(?string $installer = null): void
    {
        $installer = $this->resolveInstaller($installer);

        if ($installer === null) {
            $this->error('Installer not found');

            return;
        }

        if (! $this->confirm("Running the <em>{$installer->name}</em> installer, continue?", default: true)) {
            $this->error('Aborted.');

            return;
        }

        $installer->install();
    }

    private function resolveInstaller(?string $search): ?Installer
    {
        /** @var Installer[]|\Tempest\Support\Arr\ImmutableArray $installers */
        $installers = arr($this->installerConfig->installers)
            ->map(fn (string $installerClass) => $this->container->get($installerClass));

        if (! $search) {
            $search = $this->ask(
                question: 'Please choose an installer',
                options: $installers->mapWithKeys(fn (Installer $installer) => yield $installer::class => $installer->name)->toArray(),
            );
        }

        return $installers->first(fn (Installer $installer) => $installer::class === $search || $installer->name === $search);
    }
}
