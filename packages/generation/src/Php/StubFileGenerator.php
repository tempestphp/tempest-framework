<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use Closure;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Generation\Php\Exceptions\FileGenerationFailedException;
use Tempest\Generation\Php\Exceptions\FileGenerationWasAborted;
use Tempest\Support\Filesystem;
use Tempest\Support\Str\ImmutableString;
use Throwable;

use function Tempest\registered_namespace;
use function Tempest\Support\Namespace\to_base_class_name;
use function Tempest\Support\str;

/**
 * This class can generate a file from a stub file with additional useful methods.
 * It only works with PHP class files.
 */
final class StubFileGenerator
{
    /**
     * @param StubFile $stubFile The stub file to use for the generation. It must be of type CLASS_FILE.
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     * @param array<string, ?string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     *
     * @param array<Closure(ClassManipulator): ClassManipulator> $manipulations An array of manipulations to apply to the generated class.
     *
     * @throws FileGenerationFailedException
     */
    public function generateClassFile(
        StubFile $stubFile,
        string $targetPath,
        bool $shouldOverride = false,
        array $replacements = [],
        array $manipulations = [],
    ): void {
        try {
            if ($stubFile->type !== StubFileType::CLASS_FILE) {
                throw new FileGenerationFailedException(sprintf('The stub file must be of type CLASS_FILE, <em>%s</em> given.', $stubFile->type->name));
            }

            if (! $shouldOverride && Filesystem\is_file($targetPath)) {
                throw new FileGenerationWasAborted(sprintf('The file <em>%s</em> already exists and the operation has been aborted.', $targetPath));
            }

            $this->prepareFilesystem($targetPath);

            // Transform stub to class
            $code = Filesystem\read_file($stubFile->filePath);

            foreach ($replacements as $placeholder => $replacement) {
                if ($replacement === null) {
                    continue;
                }

                $code = str_replace($placeholder, $replacement, $code);
            }

            $namespace = registered_namespace($targetPath);
            $classname = to_base_class_name($targetPath);
            $classManipulator = new ClassManipulator(PhpFile::fromCode($code))
                ->setNamespace($namespace)
                ->setClassName($classname);

            // Run all manipulations
            $classManipulator = array_reduce(
                array: $manipulations,
                callback: static fn (ClassManipulator $manipulator, Closure $manipulation) => $manipulation($manipulator),
                initial: $classManipulator,
            );

            if ($shouldOverride && Filesystem\is_file($targetPath)) {
                Filesystem\delete_file($targetPath);
            }

            $classManipulator->save($targetPath);
        } catch (Throwable $throwable) {
            throw new FileGenerationFailedException(sprintf('The file could not be written. %s', $throwable->getMessage()), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @param StubFile $stubFile The stub file to use for the generation. It must be of type RAW_FILE.
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     * @param array<string, ?string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'dummy-content')
     *     The values are the replacements for the placeholders (e.g. 'real content')
     *
     * @param array<Closure(ImmutableString): ImmutableString> $manipulations An array of manipulations to apply to the generated file raw content.
     *
     * @throws FileGenerationFailedException
     */
    public function generateRawFile(
        StubFile $stubFile,
        string $targetPath,
        bool $shouldOverride = false,
        array $replacements = [],
        array $manipulations = [],
    ): void {
        try {
            if ($stubFile->type !== StubFileType::RAW_FILE) {
                throw new FileGenerationFailedException(sprintf('The stub file must be of type RAW_FILE, "%s" given.', $stubFile->type->name));
            }

            if (! $shouldOverride && Filesystem\is_file($targetPath)) {
                throw new FileGenerationWasAborted(sprintf('The file "%s" already exists and the operation has been aborted.', $targetPath));
            }

            $this->prepareFilesystem($targetPath);
            $fileContent = Filesystem\read_file($stubFile->filePath);

            foreach ($replacements as $placeholder => $replacement) {
                if ($replacement === null) {
                    continue;
                }

                $fileContent = str($fileContent)->replace($placeholder, $replacement);
            }

            // Run all manipulations
            $fileContent = array_reduce(
                array: $manipulations,
                callback: static fn (ImmutableString $content, Closure $manipulation) => $manipulation($content),
                initial: $fileContent,
            );

            if ($shouldOverride && Filesystem\is_file($targetPath)) {
                Filesystem\delete_file($targetPath);
            }

            Filesystem\write_file($targetPath, $fileContent);
        } catch (Throwable $throwable) {
            throw new FileGenerationFailedException(sprintf('The file could not be written. %s', $throwable->getMessage()), $throwable->getCode(), $throwable);
        }
    }

    /**
     * Prepare the directory structure for the new file.
     * It will delete the target file if it exists and we force the override.
     *
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     */
    private function prepareFilesystem(string $targetPath): void
    {
        Filesystem\create_directory_for_file($targetPath);
    }
}
