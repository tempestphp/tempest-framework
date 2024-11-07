<?php

declare(strict_types=1);

namespace Tempest\Core;

use Closure;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Enums\StubFileType;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Generation\HasGeneratorConsoleInteractions;
use function Tempest\Support\str;
use Throwable;

trait PublishesFiles
{
    use HasGeneratorConsoleInteractions;

    private array $publishedFiles = [];

    private array $publishedClasses = [];

    /**
     * Publishes a file from a source to a destination.
     *
     * @param string $source The path to the source file.
     * @param string $destination The path to the destination file.
     * @param Closure(string $source, string $destination): void|null $callback A callback to run after the file is published.
     */
    public function publish(
        string $source,
        string $destination,
        ?Closure $callback = null,
    ): void {
        try {
            if (! $this->console->confirm(
                question: sprintf('Do you want to create "%s"', $destination),
                default: true,
            )) {
                throw new FileGenerationAbortedException('Skipped.');
            }

            if (! $this->askForOverride($destination)) {
                throw new FileGenerationAbortedException('Skipped.');
            }

            $stubFile = StubFile::from($source);

            // Handle class files
            if ($stubFile->type === StubFileType::CLASS_FILE) {
                $oldClass = new ClassManipulator($source);

                $this->stubFileGenerator->generateClassFile(
                    stubFile: $stubFile,
                    targetPath: $destination,
                    shouldOverride: true,
                    manipulations: [
                        fn (ClassManipulator $class) => $class->removeClassAttribute(DoNotDiscover::class),
                    ]
                );

                $newClass = new ClassManipulator($destination);

                $this->publishedClasses[$oldClass->getClassName()] = $newClass->getClassName();
            }

            // Handle raw files
            if ($stubFile->type === StubFileType::RAW_FILE) {
                $this->stubFileGenerator->generateRawFile(
                    stubFile: $stubFile,
                    targetPath: $destination,
                    shouldOverride: true,
                );
            }

            $this->publishedFiles[] = $destination;

            if ($callback !== null) {
                $callback($source, $destination);
            }

            $this->console->success(sprintf('File successfully created at "%s".', $destination));
        } catch (FileGenerationAbortedException $exception) {
            $this->console->info($exception->getMessage());
        } catch (Throwable $throwable) {
            throw new FileGenerationFailedException(sprintf('The file could not be published. %s', $throwable->getMessage()));
        }
    }

    /**
     * Publishes the imports of the published classes.
     * Any published class that is imported in another published class will have its import updated.
     */
    public function publishImports(): void
    {
        foreach ($this->publishedFiles as $file) {
            $contents = str(file_get_contents($file));

            foreach ($this->publishedClasses as $old => $new) {
                $contents = $contents->replace($old, $new);
            }

            file_put_contents($file, $contents);
        }
    }
}
