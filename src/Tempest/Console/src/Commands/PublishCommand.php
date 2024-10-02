<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\Kernel;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\PathHelper;
use function Tempest\Support\str;
use Tempest\Validation\Rules\NotEmpty;

final readonly class PublishCommand
{
    public function __construct(
        private Kernel $kernel,
        private Console $console
    ) {
    }

    #[ConsoleCommand(
        name: 'publish',
        description: 'Publish files from vendors'
    )]
    public function publish(): void
    {
        if (! $this->kernel->publishFiles) {
            $this->console->error('No files to publish.');

            return;
        }

        $publish = $this->console->ask(
            question: 'Which files should be published?',
            options: $this->kernel->publishFiles,
            multiple: true,
            asList: true,
        );

        foreach ($publish as $file) {
            $this->console->writeln();

            $originalPath = (new ClassReflector($file))->getFilePath();
            $targetPath = $this->console->ask(
                question: sprintf('Where do you want to publish %s?', $file),
                default: PathHelper::root(), // TODO: This doesn't work?
                validation: [new NotEmpty()]
            );

            $targetPath = (string) str($targetPath)->finish('.php');

            if (file_exists($targetPath)) {
                $override = $this->console->confirm(
                    question: sprintf('%s already exists, do you want to overwrite it?', $targetPath),
                    default: false,
                );

                if (! $override) {
                    continue;
                }

                @unlink($targetPath);
            }

            if (! file_exists(dirname($targetPath))) {
                mkdir(dirname($targetPath), recursive: true);
            }

            // TODO: transform final file's namespace and remove the publish attribute
            copy($originalPath, $targetPath);

            $this->console->info(sprintf('%s published to %s.', $file, $targetPath));
        }
    }
}
