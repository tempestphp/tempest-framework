<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use function Tempest\Support\str;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Core\PublishesFiles;
use Tempest\Console\Stubs\GeneratorCommandStub;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleArgument;

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
        );

        $this->console->success(sprintf('File successfully created at <em>%s</em>.', $targetPath));
    }
}
