<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Generation\HasGeneratorCommand;
use Tempest\Console\Stubs\ControllerStub;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgument;
use Tempest\Generation\StubFileGenerator;

final class MakeControllerCommand
{
    use HasGeneratorCommand;
    
    #[ConsoleCommand(
        name       : 'make:controller',
        description: 'Create a new controller class with a basic route',
        aliases    : ['controller:make', 'controller:create', 'create:controller'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the controller class to create ( "Controller" will be suffixed )',
        )]
        string $className,
        ?string $controllerPath = null,
        ?string $controllerView = null,
    ): StubFileGenerator {
        $suggestedPath = $this->getSuggestedPath(
            className  : $className,
            pathPrefix : 'Controllers',
            classSuffix: 'Controller',
        );
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        return new StubFileGenerator(
            stubFile      : ControllerStub::class,
            targetPath    : $targetPath,
            shouldOverride: $shouldOverride,
            replacements  : [
                'dummy-path' => $controllerPath,
                'dummy-view' => $controllerView,
            ],
        );
    }
}
