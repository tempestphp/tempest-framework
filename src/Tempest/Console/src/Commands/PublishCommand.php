<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\CanBePublished;
use Tempest\Core\Composer;
use Tempest\Core\DoNotDiscover;
use Tempest\Core\Kernel;
use Tempest\Generation\ClassManipulator;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\PathHelper;
use function Tempest\Support\str;
use Tempest\Validation\Rules\NotEmpty;

final readonly class PublishCommand
{
    public function __construct(
        private Kernel $kernel,
        private Composer $composer,
        private Console $console
    ) {
    }

    #[ConsoleCommand(
        name: 'publish',
        description: 'Publish files from vendors'
    )]
    public function publish(): void
    {
        if (! $this->kernel->publishClasses) {
            $this->console->error('No files to publish.');

            return;
        }

        $publish = $this->console->ask(
            question: 'Which files should be published?',
            options: $this->kernel->publishClasses,
            multiple: true,
            asList: true,
        );

        foreach ($publish as $classToPublish) {
            $this->console->writeln();

            $suggestedPath = PathHelper::make(
                $this->composer->mainNamespace->path,
                basename((new ClassReflector($classToPublish))->getFilePath())
            );

            $targetPath = $this->console->ask(
                question: sprintf('Where do you want to publish %s?', $classToPublish),
                default: $suggestedPath,
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

            $manipulator = new ClassManipulator($classToPublish);
            $manipulator->removeClassAttribute(CanBePublished::class);
            $manipulator->removeClassAttribute(DoNotDiscover::class);
            $manipulator->updateNamespace(PathHelper::toNamespace($targetPath));

            file_put_contents($targetPath, $manipulator->print());

            $this->console->writeln();
            $this->console->success(sprintf('Published %s.', $targetPath));
        }
    }
}
