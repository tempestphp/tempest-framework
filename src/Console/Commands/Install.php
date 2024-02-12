<?php

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;

final readonly class Install
{
    public function __construct(
        private ConsoleInput $input,
        private ConsoleOutput $output,
    ) {}

    #[ConsoleCommand(name: 'install')]
    public function install(): void
    {
        $cwd = getcwd();

//        if (! $this->input->confirm(
//            question: sprintf(
//                "Installing Tempest in %s, continue?",
//                ConsoleStyle::BG_BLUE(str_replace('/', "\/", $cwd)),
//            ),
//        )) {
//            return;
//        }

        $this->copyTempest($cwd);

        $this->copyIndex($cwd);
    }

    private function copyTempest(string $cwd): void
    {
        $path = $cwd . '/tempest.php';

        if (file_exists($path)) {
            $this->output->info("{$path} already exists, skipped.");
            return;
        }

        if (! $this->input->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        copy(__DIR__ . '/../../../tempest.php', $path);

        $this->output->success("{$path} created");
    }

    private function copyIndex(string $cwd): void
    {
        $path = $cwd . '/public/index.php';

        if (file_exists($path)) {
            $this->output->info("{$path} already exists, skipped.");
            return;
        }

        if (! $this->input->confirm(
            question: sprintf("Do you want to create %s?", $path),
            default: true,
        )) {
            return;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        copy(__DIR__ . '/../../../public/index.php', $path);

        $this->output->success("{$path} created");
    }
}