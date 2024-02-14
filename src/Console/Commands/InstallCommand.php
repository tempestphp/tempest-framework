<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Interface\Console;

final readonly class InstallCommand
{
    public function __construct(
        private Console $console,
    ) {
    }

    #[ConsoleCommand(name: 'install')]
    public function install(): void
    {
        $cwd = getcwd();

        if (! $this->console->confirm(
            question: "Installing Tempest in {$cwd}, continue?",
        )) {
            return;
        }

        $this->copyTempest($cwd);

        $this->copyIndex($cwd);
    }

    private function copyTempest(string $cwd): void
    {
        $path = $cwd . '/tempest.php';

        if (file_exists($path)) {
            $this->console->error("{$path} already exists, skipped.");

            return;
        }

        if (! $this->console->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        copy(__DIR__ . '/../../../tempest', $path);

        $this->console->success("{$path} created");
    }

    private function copyIndex(string $cwd): void
    {
        $path = $cwd . '/public/index.php';

        if (file_exists($path)) {
            $this->console->error("{$path} already exists, skipped.");

            return;
        }

        if (! $this->console->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        copy(__DIR__ . '/../../../public/index.php', $path);

        $this->console->success("{$path} created");
    }
}
