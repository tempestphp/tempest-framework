<?php

declare(strict_types=1);

namespace Tempest\Database\Commands;

use InvalidArgumentException;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Enums\MigrationType;
use Tempest\Database\Stubs\MigrationStub;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\ClassManipulator;
use Tempest\Generation\DataObjects\StubFile;
use Tempest\Generation\Exceptions\FileGenerationFailedException;
use Tempest\Generation\Exceptions\FileGenerationWasAborted;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Validation\Rules\NotEmpty;

use function Tempest\Support\str;

final class MakeMigrationCommand
{
    use PublishesFiles;

    #[ConsoleCommand(
        name: 'make:migration',
        description: 'Creates a new migration file',
        aliases: ['migration:make', 'migration:create', 'create:migration'],
    )]
    public function __invoke(
        #[ConsoleArgument(
            description: 'The file name of the migration',
        )]
        string $fileName,
        #[ConsoleArgument(
            name: 'type',
            description: 'The type of the migration to create',
        )]
        MigrationType $migrationType = MigrationType::OBJECT,
    ): void {
        try {
            $stubFile = $this->getStubFileFromMigrationType($migrationType);
            $targetPath = match ($migrationType) {
                MigrationType::RAW => $this->generateRawFile($fileName, $stubFile),
                default => $this->generateClassFile($fileName, $stubFile),
            };

            $this->success(sprintf('Migration file successfully created at "%s".', $targetPath));
        } catch (FileGenerationWasAborted|FileGenerationFailedException|InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Generates a raw migration file.
     * @param string $fileName The name of the file.
     * @param StubFile $stubFile The stub file to use.
     *
     * @return string The path to the generated file.
     */
    private function generateRawFile(
        string $fileName,
        StubFile $stubFile,
    ): string {
        $now = date('Y-m-d');
        $tableName = str($fileName)->snake()->toString();
        $suggestedPath = str($this->getSuggestedPath('Dummy'))
            ->replace(
                ['Dummy', '.php'],
                [$now . '_' . $tableName, '.sql'],
            )
            ->toString();

        $targetPath = $this->promptTargetPath($suggestedPath, rules: [
            new NotEmpty(),
            new EndsWith('.sql'),
        ]);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateRawFile(
            stubFile: $stubFile,
            targetPath: $targetPath,
            shouldOverride: $shouldOverride,
            replacements: [
                'DummyTableName' => $tableName,
            ],
        );

        return $targetPath;
    }

    /**
     * Generates a class migration file.
     *
     * @param string $fileName The name of the file.
     * @param StubFile $stubFile The stub file to use.
     *
     * @return string The path to the generated file.
     */
    private function generateClassFile(
        string $fileName,
        StubFile $stubFile,
    ): string {
        $suggestedPath = $this->getSuggestedPath($fileName);
        $targetPath = $this->promptTargetPath($suggestedPath);
        $shouldOverride = $this->askForOverride($targetPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: $stubFile,
            targetPath: $targetPath,
            shouldOverride: $shouldOverride,
            replacements: [
                'dummy-date' => date('Y-m-d'),
                'dummy-table-name' => str($fileName)->snake()->toString(),
            ],
            manipulations: [
                fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
            ],
        );

        return $targetPath;
    }

    private function getStubFileFromMigrationType(MigrationType $migrationType): StubFile
    {
        try {
            return match ($migrationType) {
                MigrationType::RAW => StubFile::from(dirname(__DIR__) . '/Stubs/migration.stub.sql'),
                MigrationType::OBJECT => StubFile::from(MigrationStub::class), // @phpstan-ignore match.alwaysTrue (Because this is a guardrail for the future implementations)
                default => throw new InvalidArgumentException(sprintf('The "%s" migration type has no supported stub file.', $migrationType->value)),
            };
        } catch (InvalidArgumentException $invalidArgumentException) {
            throw new FileGenerationFailedException(sprintf('Cannot retrieve stub file: %s', $invalidArgumentException->getMessage()));
        }
    }
}
