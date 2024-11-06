<?php

declare(strict_types=1);

namespace Tempest\Generation;

use function Tempest\src_path;
use function Tempest\src_namespace;
use function Tempest\Support\arr;
use function PHPUnit\Framework\callback;
use Throwable;
use Tempest\Support\StringHelper;

use Tempest\Support\PathHelper;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Enums\StubFileType;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Console\Console;
use Closure;

/**
 * This class can generate a file from a stub file with additional useful methods.
 * It only works with PHP class files.
 */
final class StubFileGenerator
{
    public function __construct(
        private Console $console
    ) {
    }

    /**
     * @param StubFile $stubFile The stub file to use for the generation. It must be of type CLASS_FILE.
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     * 
     * @param array<Closure(ClassManipulator): ClassManipulator> $manipulations An array of manipulations to apply to the generated class.
     */
    public function generateClassFile(
        StubFile $stubFile,
        string $targetPath,
        bool $shouldOverride = false,
        array $replacements = [],
        array $manipulations = [],
    ): void {
        if ( $stubFile->type !== StubFileType::CLASS_FILE ) {
            throw new FileGenerationFailedException(sprintf('The stub file must be of type CLASS_FILE, "%s" given.', $stubFile->type->name));
        }

        try {
            $this->prepareFilesystem($targetPath, $shouldOverride);

            // Transform stub to class
            $namespace = PathHelper::toRegisteredNamespace($targetPath);
            $classname = PathHelper::toClassName($targetPath);
            $classManipulator = (new ClassManipulator($stubFile->filePath))
                ->setNamespace($namespace)
                ->setClassName($classname);

            foreach ($replacements as $placeholder => $replacement) {
                if (! is_string($replacement)) {
                    continue;
                }

                $classManipulator->manipulate(fn (StringHelper $code) => $code->replace($placeholder, $replacement));
            }

            // Run all manipulations
            $classManipulator = array_reduce(
                array: $manipulations,
                initial: $classManipulator,
                callback: fn ( ClassManipulator $manipulator, Closure $manipulation ) => $manipulation($manipulator) 
            );

            $classManipulator->save($targetPath);
        } catch (Throwable $throwable) {
            throw new FileGenerationFailedException(sprintf('The file could not be written. %s', $throwable->getMessage()));
        }
    }

    /**
     * @param StubFile $stubFile The stub file to use for the generation. It must be of type RAW_FILE.
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'dummy-content')
     *     The values are the replacements for the placeholders (e.g. 'real content')
     * 
     * @param array<Closure(StringHelper): StringHelper> $manipulations An array of manipulations to apply to the generated file raw content.
     */
    public function generateRawFile(
        StubFile $stubFile,
        string $targetPath,
        bool $shouldOverride = false,
        array $replacements = [],
        array $manipulations = [],
    ): void {
        if ( $stubFile->type !== StubFileType::RAW_FILE ) {
            throw new FileGenerationFailedException(sprintf('The stub file must be of type RAW_FILE, "%s" given.', $stubFile->type->name));
        }

        try {
            $this->prepareFilesystem($targetPath, $shouldOverride);

            $fileContent = file_get_contents($stubFile->filePath);

            foreach ($replacements as $placeholder => $replacement) {
                if (! is_string($replacement)) {
                    continue;
                }

                $fileContent = str($fileContent)->replace($placeholder, $replacement);
            }

            // Run all manipulations
            $fileContent = array_reduce(
                array: $manipulations,
                initial: $fileContent,
                callback: fn ( StringHelper $content, Closure $manipulation ) => $manipulation($content) 
            );

            file_put_contents($targetPath, $fileContent);
        } catch (Throwable $throwable) {
            throw new FileGenerationFailedException(sprintf('The file could not be written. %s', $throwable->getMessage()));
        }
    }

    /**
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param string|class-string $stubFile The stub file path to use for the generation.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     * @param array<Closure> $manipulations An array of manipulations to apply to the generated class.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     */
    public function generate(
        string $targetPath,
        string $stubFile,
        array $replacements = [],
        array $manipulations = [],
        bool $shouldOverride = false,
    ): void {
        try {
            $this->prepareFilesystem($targetPath, $shouldOverride);
            $file_path = $this->writeFile($targetPath, $stubFile, $replacements, $manipulations);

            $this->console->success(sprintf('File successfully created at "%s".', $file_path));
        } catch (Throwable $throwable) {
            $this->console->error($throwable->getMessage());
        }
    }

    /**
     * Prepare the directory structure for the new file.
     * It will delete the target file if it exists and we force the override.
     *
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     * 
     * @throws FileGenerationFailedException If the operation has been aborted.
     */
    private function prepareFilesystem(
        string $targetPath,
        bool $shouldOverride = false,
    ): void {
        // Delete the file if it exists and we force the override
        if (file_exists($targetPath)) {
            if (! $shouldOverride) {
                throw new FileGenerationAbortedException(sprintf('The file "%s" already exists and the operation has been aborted.', $targetPath));
            }

            @unlink($targetPath);
        }

        // Recursively create directories before writing the file
        $directory = dirname($targetPath);
        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }
    }

    /**
     * Write the file to the target path.
     *
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param string|class-string $stubFile The stub file path to use for the generation.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     * @param array<Closure> $manipulations An array of manipulations to apply to the generated class.
     * 
     * @throws FileGenerationFailedException If the file could not be written.
     *
     * @return string The path where the file was written.
     */
    private function writeFile(
        string $targetPath,
        string $stubFile,
        array $replacements = [],
        array $manipulations = [],
    ): string {
        try {
            // Transform stub to class
            $namespace = PathHelper::toRegisteredNamespace($targetPath);
            $classname = PathHelper::toClassName($targetPath);
            $classManipulator = (new ClassManipulator($stubFile))
                ->setNamespace($namespace)
                ->setClassName($classname);

            foreach ($replacements as $placeholder => $replacement) {
                if (! is_string($replacement)) {
                    continue;
                }

                $classManipulator->manipulate(fn (StringHelper $code) => $code->replace($placeholder, $replacement));
            }

            // Run all manipulations
            $classManipulator = array_reduce(
                array: $manipulations,
                initial: $classManipulator,
                callback: fn ( ClassManipulator $manipulator, Closure $manipulation ) => $manipulation($manipulator) 
            );

            $classManipulator->save($targetPath);

            return $targetPath;
        } catch (\Throwable $th) {
            throw new FileGenerationFailedException(sprintf('The file could not be written. %s', $th->getMessage()));
        }
    }
}
