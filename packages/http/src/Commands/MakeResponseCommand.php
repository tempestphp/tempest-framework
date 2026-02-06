<?php

declare(strict_types=1);

namespace Tempest\Http\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Http\Stubs\ResponseStub;

final class MakeResponseCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:response',
        description: 'Creates a new response class',
        aliases: ['response:make', 'response:create', 'create:response'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the response class to create',
        )]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: StubFile::from(ResponseStub::class),
            targetPath: $targetPath,
            shouldOverride: $shouldOverride,
        );

        $this->console->writeln();
        $this->console->success(sprintf('Response successfully created at "%s".', $targetPath));
    }
}
