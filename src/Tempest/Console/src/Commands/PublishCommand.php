<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\PublishConfig;
use Tempest\Core\CanBePublished;
use Tempest\Core\Composer;
use Tempest\Core\DoNotDiscover;
use Tempest\Generation\ClassManipulator;
use Tempest\Reflection\ClassReflector;
use function Tempest\Support\arr;
use Tempest\Support\PathHelper;
use function Tempest\Support\str;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Validation\Rules\NotEmpty;

final readonly class PublishCommand
{
    public function __construct(
        private PublishConfig $publishConfig,
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
        $filesToPublish = [
            ...$this->publishConfig->publishClasses,
            ...$this->publishConfig->publishFiles,
        ];

        if ($filesToPublish === []) {
            $this->console->error('No files to publish.');

            return;
        }

        $selectedFiles = $this->console->ask(
            question: 'Which files should be published?',
            multiple: true,
            options: arr($filesToPublish)
                ->mapWithKeys(function (string $file) {
                    if (class_exists($file)) {
                        yield $file => "[Class] {$file}";
                    }

                    // TODO: maybe format the file path in a friendlier way
                    yield $file => "[File] {$file}";
                })
                ->toArray(),
        );

        $publishedFiles = [];

        foreach (array_keys($selectedFiles) as $file) {
            $this->console->writeln();

            $publishedPath = match (class_exists($file)) {
                true => $this->publishClass($file),
                false => $this->publishFile($file),
            };

            if ($publishedPath === false) {
                continue;
            }

            $publishedFiles[] = $publishedPath;

            $this->console->writeln();
            $this->console->success(sprintf('Published %s.', $publishedPath));
        }

        $this->console->writeln();
        $this->console->success(sprintf('Published %s %s.', count($publishedFiles), str('file')->pluralize(count($publishedFiles))));
    }

    private function publishClass(string $class): string|false
    {
        $suggestedPath = PathHelper::make(
            $this->composer->mainNamespace->path,
            basename((new ClassReflector($class))->getFilePath())
        );

        $targetPath = $this->promptTargetPath(
            name: $class,
            suggested: $suggestedPath,
            rules: [new NotEmpty(), new EndsWith('.php')]
        );

        if (! $this->prepareFilesystem($targetPath)) {
            return false;
        }

        $manipulator = new ClassManipulator($class);
        $manipulator->removeClassAttribute(CanBePublished::class);
        $manipulator->removeClassAttribute(DoNotDiscover::class);
        $manipulator->setNamespace(PathHelper::toNamespace($targetPath));

        file_put_contents($targetPath, $manipulator->print());

        return $targetPath;
    }

    private function publishFile(string $file): string|false
    {
        $suggestedPath = PathHelper::make(
            $this->composer->mainNamespace->path,
            basename($file)
        );

        $targetPath = $this->promptTargetPath(
            name: $file,
            suggested: $suggestedPath,
            rules: [new NotEmpty()]
        );

        if (! $this->prepareFilesystem($targetPath)) {
            return false;
        }

        copy($file, $targetPath);

        return $targetPath;
    }

    private function prepareFilesystem(string $targetPath): bool
    {
        if (file_exists($targetPath)) {
            $override = $this->console->confirm(
                question: sprintf('%s already exists, do you want to overwrite it?', $targetPath),
                default: false,
            );

            if (! $override) {
                return false;
            }

            @unlink($targetPath);
        }

        if (! file_exists(dirname($targetPath))) {
            mkdir(dirname($targetPath), recursive: true);
        }

        return true;
    }

    private function promptTargetPath(string $name, string $suggested, array $rules = []): string
    {
        return $this->console->ask(
            question: sprintf('Where do you want to publish %s?', $name),
            default: $suggested,
            validation: $rules
        );
    }
}
