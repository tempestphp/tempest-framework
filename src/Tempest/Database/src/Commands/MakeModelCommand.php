<?php

declare(strict_types=1);

namespace Tempest\Database\Commands;

use Tempest\Generation\HasGeneratorConsoleInteractions;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Database\Stubs\DatabaseModelStub;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgument;

final class MakeModelCommand
{
    use HasGeneratorConsoleInteractions;

    #[ConsoleCommand(
        name: 'make:model',
        description: 'Creates a new model class',
        aliases: ['model:make', 'model:create', 'create:model'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the model class to create',
        )]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        try {
            
            $this->stubFileGenerator->generateClassFile(
                stubFile: StubFile::fromClassString( DatabaseModelStub::class ),
                targetPath: $targetPath,
                shouldOverride: $shouldOverride,
            );
    
            $this->console->success(sprintf('File successfully created at "%s".', $targetPath));
        } catch (FileGenerationAbortedException|FileGenerationFailedException $e) {
            $this->console->error($e->getMessage());
        }
    }
}
