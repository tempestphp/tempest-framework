<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Generation\StubFileGenerator;
use Tempest\Generation\HasGeneratorCommand;
use Tempest\Console\Stubs\ModelStub;
use Tempest\Console\GeneratorCommand;

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
    ): StubFileGenerator
    {
        $suggestedPath = $this->getSuggestedPath(
            className  : $className,
            pathPrefix : 'Models',
            classSuffix: 'Model',
        );
        $targetPath     = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);
        
        // The Discovery may use the prepareFilesystem method to create the directories
        return new StubFileGenerator(
            stubFile      : ModelStub::class,
            targetPath    : $suggestedPath,
            shouldOverride: $shouldOverride,
            replacements  : [
                'DummyNamespace' => $this->composer->mainNamespace->path,
                'DummyClass'     => $className,
            ],
        );
    }
}
