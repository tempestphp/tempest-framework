<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Tempest\Support\StringHelper;
use Tempest\Support\PathHelper;
use Tempest\Generation\Exceptions\StubFileGenerationFailedException;
use Tempest\Console\Console;

/**
 * This class can generate a file from a stub file with additional useful methods.
 * It only works with PHP class files.
 */
final class StubFileGenerator
{
    public function __construct(
        private Console $console
    ) {}

    /**
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param class-string $stubFile The stub file to use for the generation.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     */
    public function generate(
        string $targetPath,
        string $stubFile,
        array $replacements = [],
        bool $shouldOverride = false,
    ): void
    {
        try {
            $this->prepareFilesystem($targetPath, $shouldOverride);
            $file_path = $this->writeFile($targetPath, $stubFile, $replacements);
        } catch ( StubFileGenerationFailedException $e ) {
            $this->console->error( $e->getMessage() );
        }
        
        $this->console->success(sprintf('File successfully created at "%s".', $file_path));
    }

    /**
     * Prepare the directory structure for the new file.
     * It will delete the target file if it exists and we force the override.
     * 
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     *
     * @return bool Whether the filesystem is ready to write the file.
     */
    private function prepareFilesystem(
        string $targetPath,
        bool $shouldOverride = false,
    ): void
    {
        // Delete the file if it exists and we force the override
        if (file_exists($targetPath)) {
            if (! $shouldOverride) {
                return;
            }

            @unlink($targetPath);
        }

        // Recursively create directories before writing the file
        if (! file_exists(dirname($targetPath))) {
            mkdir(dirname($targetPath), recursive: true);
        }
    }

    /**
     * Write the file to the target path.
     * 
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param string $stubFile The stub file to use for the generation.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     *
     * @throws StubFileGenerationFailedException If the file could not be written.
     * 
     * @return string The path where the file was written.
     */
    private function writeFile(
        string $targetPath,
        string $stubFile,
        array $replacements = [],
    ): 
    {
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

        // Write the file
        $is_success = (bool) file_put_contents(
            $targetPath,
            $classManipulator->print()
        );

        if (! $is_success) {
            throw new StubFileGenerationFailedException('The file could not be written.');
        }

        return $targetPath;
    }
}