<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use InvalidArgumentException;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Enums\ConfigType;
use Tempest\Core\PublishesFiles;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Exceptions\FileGenerationAbortedException;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use function Tempest\Support\str;

final class MakeConfigCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:config',
        description: 'Creates a new config class',
        aliases: ['config:make', 'config:create', 'create:config'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            name: 'type',
            help: 'The type of the config to create',
        )]
        ConfigType $configType,
    ): void {
        try {
            $stubFile = $this->getStubFileFromConfigType($configType);
            $suggestedPath = str($this->getSuggestedPath('Dummy'))
                ->replace('Dummy', $configType->value . '.config')
                ->toString();
            $targetPath = $this->promptTargetPath($suggestedPath);
            $shouldOverride = $this->askForOverride($targetPath);

            $this->stubFileGenerator->generateRawFile(
                stubFile: $stubFile,
                targetPath: $targetPath,
                shouldOverride: $shouldOverride,
            );

            $this->success(sprintf('Middleware successfully created at "%s".', $targetPath));
        } catch (FileGenerationAbortedException|FileGenerationFailedException|InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    private function getStubFileFromConfigType(ConfigType $configType): StubFile
    {
        try {
            $stubPath = dirname(__DIR__) . '/Stubs';

            return match ($configType) {
                ConfigType::CONSOLE => StubFile::from($stubPath . '/console.config.stub.php'),
                ConfigType::CACHE => StubFile::from($stubPath . '/cache.config.stub.php'),
                ConfigType::LOG => StubFile::from($stubPath . '/log.config.stub.php'),
                ConfigType::COMMAND_BUS => StubFile::from($stubPath . '/command-bus.config.stub.php'),
                ConfigType::EVENT_BUS => StubFile::from($stubPath . '/event-bus.config.stub.php'),
                ConfigType::VIEW => StubFile::from($stubPath . '/view.config.stub.php'),
                ConfigType::BLADE => StubFile::from($stubPath . '/blade.config.stub.php'),
                ConfigType::TWIG => StubFile::from($stubPath . '/twig.config.stub.php'),
                ConfigType::DATABASE => StubFile::from($stubPath . '/database.config.stub.php'), // @phpstan-ignore match.alwaysTrue (Because this is a guardrail for the future implementations)
                default => throw new InvalidArgumentException(sprintf('The "%s" config type has no supported stub file.', $configType->value)),
            };
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new FileGenerationFailedException(sprintf('Cannot retrieve stub file: %s', $invalidArgumentException->getMessage()));
        }
    }
}
