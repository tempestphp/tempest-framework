<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Discovery\DoNotDiscover;
use Tempest\Generation\DataObjects\StubFile;

#[DoNotDiscover]
final class GeneratorCommandStub
{
    use PublishesFiles;

    #[ConsoleCommand(name: 'dummy-command-slug')]
    public function __invoke(
        #[ConsoleArgument(description: 'The name of the class to create')]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: StubFile::from('MyStubClass::class'),
            targetPath: $targetPath,
            shouldOverride: $shouldOverride,
        );

        $this->console->writeln();
        $this->console->success(sprintf('File successfully created at <file="%s"/>.', $targetPath));
    }
}
