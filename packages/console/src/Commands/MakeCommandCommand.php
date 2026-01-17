<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Stubs\CommandStub;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\DataObjects\StubFile;

use function Tempest\Support\str;

final class MakeCommandCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:command',
        description: 'Creates a new command class',
        aliases: ['command:make', 'command:create', 'create:command'],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'The name of the command class to create')]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: StubFile::from(CommandStub::class),
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
