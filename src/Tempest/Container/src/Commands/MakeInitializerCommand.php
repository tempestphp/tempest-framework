<?php

declare(strict_types=1);

namespace Tempest\Container\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Singleton;
use Tempest\Container\Stubs\InitializerStub;
use Tempest\Core\PublishesFiles;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Exceptions\FileGenerationFailedException;

final class MakeInitializerCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:initializer',
        description: 'Creates a new initializer class',
        aliases: ['initializer:make', 'initializer:create', 'create:initializer'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the initializer class to create',
        )]
        string $className,
        #[ConsoleArgument(
            name: 'singleton',
            help: 'Whether the initializer should be a singleton',
        )]
        bool $isSingleton = false,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        try {
            $this->stubFileGenerator->generateClassFile(
                stubFile: StubFile::from(InitializerStub::class),
                targetPath: $targetPath,
                shouldOverride: $shouldOverride,
                manipulations: [
                    function (ClassManipulator $stubClass) use ($isSingleton) {
                        if ($isSingleton) {
                            $stubClass->addMethodAttribute('initialize', Singleton::class);
                        }

                        return $stubClass;
                    },
                ]
            );

            $this->console->success(sprintf('Initializer successfully created at "%s".', $targetPath));
        } catch (FileGenerationAbortedException|FileGenerationFailedException $e) {
            $this->console->error($e->getMessage());
        }
    }
}
