<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Support\PathHelper;

final readonly class InstallCommand
{
    public function __construct(
        private Console $console,
    ) {
    }

    #[ConsoleCommand(
        name: 'install',
        description: 'Interactively install Tempest in your project'
    )]
    public function install(bool $force = false): void
    {
        $cwd = getcwd();

        if (! $force && ! $this->console->confirm(
            question: "Installing Tempest in {$cwd}, continue?",
        )) {
            return;
        }

        $this->copyTempest($cwd, $force);

        $this->copyIndex($cwd, $force);

        $this->copyEnvExample($cwd, $force);

        $this->copyEnv($cwd, $force);
    }

    private function copyEnv(string $cwd, bool $force): void
    {
        $path = PathHelper::make($cwd . '/.env');

        if (file_exists($path)) {
            $this->console->error("{$path} already exists, skipped.");

            return;
        }

        if (! $force && ! $this->console->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        copy(__DIR__ . '/../../../../../.env.example', $path);

        $this->console->success("{$path} created");
    }

    private function copyEnvExample(string $cwd, bool $force): void
    {
        $path = PathHelper::make($cwd . '/.env.example');

        if (file_exists($path)) {
            $this->console->error("{$path} already exists, skipped.");

            return;
        }

        if (! $force && ! $this->console->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        copy(__DIR__ . '/../../../../../.env.example', $path);

        $this->console->success("{$path} created");
    }

    private function copyTempest(string $cwd, bool $force): void
    {
        $path = PathHelper::make($cwd . '/tempest');

        if (file_exists($path)) {
            $this->console->error("{$path} already exists, skipped.");

            return;
        }

        if (! $force && ! $this->console->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        copy(__DIR__ . '/../../bin/tempest', $path);

        if (PHP_OS_FAMILY !== 'Windows') {
            exec("chmod +x {$path}");
        }

        $this->console->success("{$path} created");
    }

    private function copyIndex(string $cwd, bool $force): void
    {
        $path = PathHelper::make($cwd . '/public/index.php');

        if (file_exists($path)) {
            $this->console->error("{$path} already exists, skipped.");

            return;
        }

        if (! $force && ! $this->console->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        copy(__DIR__ . '/index.php', $path);

        $this->console->success("{$path} created");
    }
}
