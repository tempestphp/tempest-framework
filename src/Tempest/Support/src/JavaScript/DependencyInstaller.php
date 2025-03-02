<?php

declare(strict_types=1);

namespace Tempest\Support\JavaScript;

use Symfony\Component\Process\Process;
use Tempest\Console\Console;
use Tempest\Validation\Rules\Enum;
use function Tempest\Support\Arr\wrap;

/**
 * Helps with installing JavaScript dependencies in a directory.
 */
final class DependencyInstaller
{
    public function __construct(
        private readonly Console $console,
    ) {
    }

    /**
     * Installs the specified JavaScript dependencies.
     * The package manager will be detected from the lockfile present in `$cwd`. If none found, it will be prompted to the user.
     */
    public function installDependencies(string $cwd, string|array $dependencies, bool $dev = false): void
    {
        /** @var PackageManager */
        $packageManager = PackageManager::detect($cwd) ?? $this->console->ask(
            question: 'Which package manager do you wish to use?',
            options: PackageManager::class,
            default: PackageManager::BUN,
            validation: [
                new Enum(PackageManager::class),
            ],
        );

        $this->console->task('Installing dependencies', $this->getInstallProcess($packageManager, $cwd, $dependencies, $dev));
    }

    /**
     * Installs dependencies without interacting with the console.
     */
    public function silentlyInstallDependencies(string $cwd, string|array $dependencies, bool $dev = false, ?PackageManager $defaultPackageManager = null): void
    {
        $install = $this->getInstallProcess(
            packageManager: PackageManager::detect($cwd) ?? $defaultPackageManager,
            cwd: $cwd,
            dependencies: $dependencies,
            dev: $dev,
        );

        $install->mustRun();
    }

    /**
     * Gets the `Process` instance that will install the specified dependencies.
     */
    private function getInstallProcess(PackageManager $packageManager, string $cwd, string|array $dependencies, bool $dev = false): Process
    {
        return new Process([
            $packageManager->getBinaryName(),
            $packageManager->getInstallCommand(),
            $dev ? '-D' : '',
            ...wrap($dependencies),
        ], $cwd);
    }
}
