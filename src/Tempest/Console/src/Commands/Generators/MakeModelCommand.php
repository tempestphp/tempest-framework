<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Console\GeneratorCommand;
use Tempest\Console\Stubs\DatabaseModelStub;
use Tempest\Console\Stubs\ModelStub;
use Tempest\Generation\HasGeneratorCommand;
use Tempest\Generation\StubFileGenerator;

final class MakeModelCommand
{
    use HasGeneratorCommand;

    #[GeneratorCommand(
        name       : 'make:model',
        description: 'Create a new model class',
        aliases    : ['model:make', 'model:create', 'create:model'],
    )]
    public function __invoke(
        string $className,
        bool $isDatabaseModel = false,
    ): StubFileGenerator {
        $suggestedPath = $this->getSuggestedPath(
            className  : $className,
            pathPrefix : 'Models',
            classSuffix: 'Model',
        );
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        return new StubFileGenerator(
            stubFile      : $isDatabaseModel ? DatabaseModelStub::class : ModelStub::class,
            targetPath    : $targetPath,
            shouldOverride: $shouldOverride,
        );
    }
}
