<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Generation\StubFileGenerator;
use Tempest\Generation\HasGeneratorCommand;
use Tempest\Console\Stubs\ModelStub;
use Tempest\Console\GeneratorCommand;
use Tempest\Console\Stubs\DatabaseModelStub;

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
    ): void
    {
        $suggestedPath = $this->getSuggestedPath(
            className  : $className,
            pathPrefix : 'Models',
            classSuffix: 'Model',
        );
        $targetPath     = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);
        
        $generator = new StubFileGenerator(
            stubFile      : $isDatabaseModel ? DatabaseModelStub::class : ModelStub::class,
            targetPath    : $suggestedPath,
            shouldOverride: $shouldOverride,
        );
        $generator->generate();
    }
}
