<?php

declare(strict_types=1);

namespace Tempest\Console\Commands\Generators;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Stubs\ControllerStub;
use Tempest\Generation\HasGeneratorCommand;
use Tempest\Generation\StubFileGenerator;

final class MakeControllerCommand
{
    use HasGeneratorCommand;

    public function __construct(
        private StubFileGenerator $stubFileGenerator
    ) {
    }

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
        #[ConsoleArgument(
            name: 'path',
            help: 'The route path inside the controller',
        )]
        ?string $controllerPath = null,
        #[ConsoleArgument(
            name: 'view',
            help: 'The view name to return in the controller',
        )]
        ?string $controllerView = null,
    ): void {
        $suggestedPath = $this->getSuggestedPath(
            className  : $className,
            pathPrefix : 'Controllers',
            classSuffix: 'Controller',
        );
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generate(
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
