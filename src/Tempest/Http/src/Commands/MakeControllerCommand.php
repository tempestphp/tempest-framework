<?php

declare(strict_types=1);

namespace Tempest\Http\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Http\Stubs\ControllerStub;

final class MakeControllerCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:controller',
        description: 'Creates a new controller class with a route',
        aliases: ['controller:make', 'controller:create', 'create:controller'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the controller class to create',
        )]
        string $className,
        #[ConsoleArgument(
            name: 'path',
            help: 'The path of the route',
        )]
        ?string $controllerPath = null,
        #[ConsoleArgument(
            name: 'view',
            help: 'The name of the view returned from the controller',
        )]
        ?string $controllerView = null,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        try {
            $this->stubFileGenerator->generateClassFile(
                stubFile: StubFile::from(ControllerStub::class),
                targetPath: $targetPath,
                shouldOverride: $shouldOverride,
                replacements: [
                    'dummy-path' => $controllerPath,
                    'dummy-view' => $controllerView,
                ],
            );

            $this->success(sprintf('Controller successfully created at "%s".', $targetPath));
        } catch (FileGenerationAbortedException|FileGenerationFailedException $e) {
            $this->error($e->getMessage());
        }
    }
}
