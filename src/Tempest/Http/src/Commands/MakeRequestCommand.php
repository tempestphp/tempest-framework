<?php

declare(strict_types=1);

namespace Tempest\Http\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Http\Stubs\RequestStub;

final class MakeRequestCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:request',
        description: 'Creates a new request class',
        aliases: ['request:make', 'request:create', 'create:request'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            help: 'The name of the request class to create',
        )]
        string $className,
    ): void {
        $suggestedPath = $this->getSuggestedPath($className);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        try {
            $this->stubFileGenerator->generateClassFile(
                stubFile: StubFile::from(RequestStub::class),
                targetPath: $targetPath,
                shouldOverride: $shouldOverride,
            );

            $this->success(sprintf('Request successfully created at "%s".', $targetPath));
        } catch (FileGenerationAbortedException|FileGenerationFailedException $e) {
            $this->error($e->getMessage());
        }
    }
}
