<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Stubs\DatabaseModelStub;
use Tempest\Console\Stubs\ModelStub;
use Tempest\Generation\HasGeneratorCommand;

final class MakeModelCommand
{
    use HasGeneratorCommand;

    #[ConsoleCommand(
        name: 'make:model',
        description: 'Creates a new model class',
        aliases: ['model:make', 'model:create', 'create:model'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the model class to create ( "Model" will be suffixed )',
        )]
        string $className,
        #[ConsoleArgument(
            help: 'Whether the model is a database model',
        )]
        bool $isDatabaseModel = false,
    ): void {
        $suggestedPath = $this->getSuggestedPath(
            className: $className,
            pathPrefix: 'Models',
            classSuffix: 'Model',
        );
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generate(
            stubFile      : $isDatabaseModel ? DatabaseModelStub::class : ModelStub::class,
            targetPath    : $targetPath,
            shouldOverride: $shouldOverride,
        );
    }
}
