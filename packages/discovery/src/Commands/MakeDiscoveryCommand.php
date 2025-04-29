<?php

declare(strict_types=1);

namespace Tempest\Discovery\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Discovery\Stubs\DiscoveryStub;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\DataObjects\StubFile;

final class MakeDiscoveryCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:discovery',
        description: 'Creates a new discovery class',
        aliases: ['discovery:make', 'discovery:create', 'create:discovery'],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'The name of the discovery class to create')]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: StubFile::from(DiscoveryStub::class),
            targetPath: $targetPath,
            shouldOverride: $shouldOverride,
            manipulations: [
                fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
            ],
        );

        $this->console->writeln();
        $this->console->success(sprintf('File successfully created at <file="%s"/>.', $targetPath));
    }
}
