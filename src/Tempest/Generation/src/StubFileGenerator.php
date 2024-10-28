<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Tempest\Console\Console;
use function Tempest\get;
use Tempest\Support\PathHelper;
use Tempest\Support\StringHelper;

/**
 * This class can generate a file from a stub file with additional useful methods.
 * It only works with PHP class files.
 */
final class StubFileGenerator
{
    private Console $console;

    /**
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param class-string $stubFile The stub file to use for the generation.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     * @param bool $shouldOverride Whether the generator should override the file if it already exists.
     */
    public function __construct(
        private readonly string $targetPath,
        private readonly string $stubFile,
        private readonly array $replacements = [],
        private readonly bool $shouldOverride = false,
    ) {
        $this->console = get(Console::class);
    }

    public function generate(): void
    {
        if (! $this->prepareFilesystem()) {
            $this->console->error('The operation has been aborted.');

            return;
        }

        if (! $this->writeFile()) {
            $this->console->error('The file could not be written.');

            return;
        }

        $this->console->success(sprintf('File successfully created at "%s".', $this->targetPath));
    }

    /**
     * Write the file to the target path.
     *
     * @return bool Whether the file was written successfully.
     */
    private function writeFile(): bool
    {
        // Transform stub to class
        $namespace = PathHelper::toRegisteredNamespace($this->targetPath);
        $classname = PathHelper::toClassName($this->targetPath);
        $classManipulator = (new ClassManipulator($this->stubFile))
            ->setNamespace($namespace)
            ->setClassName($classname);

        foreach ($this->replacements as $placeholder => $replacement) {
            if (! is_string($replacement)) {
                continue;
            }

            $classManipulator->manipulate(fn (StringHelper $code) => $code->replace($placeholder, $replacement));
        }

        // Write the file
        return (bool) file_put_contents(
            $this->targetPath,
            $classManipulator->print()
        );
    }

    /**
     * Prepare the directory structure for the new file.
     * It will delete the target file if it exists and we force the override.
     *
     * @return bool Whether the filesystem is ready to write the file.
     */
    private function prepareFilesystem(): bool
    {
        // Delete the file if it exists and we force the override
        if (file_exists($this->targetPath)) {
            if (! $this->shouldOverride) {
                return false;
            }

            @unlink($this->targetPath);
        }

        // Recursively create directories before writing the file
        if (! file_exists(dirname($this->targetPath))) {
            mkdir(dirname($this->targetPath), recursive: true);
        }

        return true;
    }
}
