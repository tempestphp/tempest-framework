<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Stubs\GeneratorCommandStub;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\DataObjects\StubFile;

use function Tempest\Support\str;

final class MakeGeneratorCommandCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:generator-command',
        description: 'Creates a new generator command class',
        aliases: ['generator-command:make', 'generator-command:create', 'create:generator-command'],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'The name of the generator command class to create')]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: StubFile::from(GeneratorCommandStub::class),
            targetPath: $targetPath,
            shouldOverride: $shouldOverride,
            replacements: [
                'dummy-command-slug' => str($className)->kebab()->toString(),
            ],
            manipulations: [
                fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
            ],
        );

        $this->console->writeln();
        $this->console->success(sprintf('File successfully created at <file="%s"/>.', $targetPath));
    }
}
