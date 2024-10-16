<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Tempest\Console\Console;

use function Tempest\get;

/**
 * This class can generate a file from a stub file with additional useful methods.
 * It only works with PHP class files.
 */
final class StubFileGenerator
{
    /**
     * @param string $targetPath The path where the generated file will be saved including the filename and extension.
     * @param class-string $stubFile The stub file to use for the generation.
     * @param array<string, string> $replacements An array of key-value pairs to replace in the stub file.
     *     The keys are the placeholders in the stub file (e.g. 'DummyNamespace')
     *     The values are the replacements for the placeholders (e.g. 'App\Models')
     * @param boolean $shouldOverride Whether the generator should override the file if it already exists.
     */
    public function __construct(
        protected readonly string $targetPath,
        protected readonly string $stubFile,
        protected readonly array $replacements = [],
        protected readonly bool $shouldOverride = false,
    ) {}

    public function generate(): void {
        $console = get(Console::class);

        $console->success('File generated successfully!');

        dd('Generating file...' );
    }
}
