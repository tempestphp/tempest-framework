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

final readonly class InstallCommand
{
    use HasConsole;

    public function __construct(
        private InstallerConfig $installerConfig,
        private Console $console,
        private Container $container,
    ) {
    }

    #[ConsoleCommand(name: 'install', middleware: [ForceMiddleware::class])]
    public function __invoke(?string $installer = null): void
    {
        $installer = $this->resolveInstaller($installer);

        if (! $this->confirm("Running the `{$installer->getName()}` installer, continue?")) {
            $this->error('Aborted');

            return;
        }

        $installer->publishFiles();

        $this->success('Done');
    }

    private function resolveInstaller(?string $installer): Installer
    {
        if ($installer) {
            foreach ($this->installerConfig->installers as $searchInstallerClass) {
                /** @var Installer $searchInstaller */
                $searchInstaller = $this->container->get($searchInstallerClass);

                if ($installer === $searchInstaller->getName()) {
                    return $searchInstaller;
                }
            }
        }

        $installerClass = $this->ask(
            question: 'Please choose an installer',
            options: $this->installerConfig->installers,
        );

        return $this->container->get($installerClass);
    }
}
