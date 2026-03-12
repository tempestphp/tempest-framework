<?php

declare(strict_types=1);

namespace Tempest\Router\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Router\Stubs\ControllerStub;

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final class MakeControllerCommand
    {
        use PublishesFiles;

        #[ConsoleCommand(
            name: 'make:controller',
            description: 'Creates a new controller class with a route',
            aliases: ['controller:make', 'controller:create', 'create:controller'],
        )]
        public function __invoke(
            #[ConsoleArgument(description: 'The name of the controller class to create')]
            string $className,
            #[ConsoleArgument(name: 'path', description: 'The path of the route')]
            ?string $controllerPath = null,
            #[ConsoleArgument(name: 'view', description: 'The name of the view returned from the controller')]
            ?string $controllerView = null,
        ): void {
            $suggestedPath = $this->getSuggestedPath($className);
            $targetPath = $this->promptTargetPath($suggestedPath);
            $shouldOverride = $this->askForOverride($targetPath);

            $this->stubFileGenerator->generateClassFile(
                stubFile: StubFile::from(ControllerStub::class),
                targetPath: $targetPath,
                shouldOverride: $shouldOverride,
                replacements: [
                    'dummy-path' => $controllerPath,
                    'dummy-view' => $controllerView,
                ],
                manipulations: [
                    fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
                ],
            );

            $this->console->writeln();
            $this->console->success(sprintf('File successfully created at <file="%s"/>.', $targetPath));
        }
    }
}
