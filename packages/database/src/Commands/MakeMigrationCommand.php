<?php

declare(strict_types=1);

namespace Tempest\Database\Commands;

use InvalidArgumentException;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Enums\MigrationType;
use Tempest\Database\Stubs\ObjectMigrationStub;
use Tempest\Database\Stubs\UpMigrationStub;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Generation\Php\Exceptions\FileGenerationFailedException;
use Tempest\Generation\Php\Exceptions\FileGenerationWasAborted;
use Tempest\Support\Str;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Validation\Rules\IsNotEmptyString;

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
        #[ConsoleArgument(description: 'The file name of the migration')]
        string $fileName,
        #[ConsoleArgument(name: 'type', description: 'The type of the migration to create')]
        MigrationType $migrationType = MigrationType::OBJECT,
    ): void {
        try {
            $stub = match ($migrationType) {
                MigrationType::RAW => StubFile::from(dirname(__DIR__) . '/Stubs/migration.stub.sql'),
                MigrationType::OBJECT => StubFile::from(ObjectMigrationStub::class),
                MigrationType::UP => StubFile::from(UpMigrationStub::class),
            };

            $targetPath = match ($migrationType) {
                MigrationType::RAW => $this->generateRawFile($fileName, $stub),
                default => $this->generateClassFile($fileName, $stub),
            };

            $this->success(sprintf('Migration file successfully created at "%s".', $targetPath));
        } catch (FileGenerationWasAborted|FileGenerationFailedException|InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    private function generateRawFile(string $filename, StubFile $stubFile): string
    {
        $tableName = str($filename)
            ->snake()
            ->stripStart('create')
            ->stripEnd('table')
            ->stripStart('_')
            ->stripEnd('_')
            ->toString();

        $filename = str($filename)
            ->start('create_')
            ->finish('_table')
            ->toString();

        $suggestedPath = Str\replace(
            string: $this->getSuggestedPath('Dummy'),
            search: ['Dummy', '.php'],
            replace: [date('Y-m-d') . '_' . $filename, '.sql'],
        );

        $targetPath = $this->promptTargetPath($suggestedPath, rules: [
            new IsNotEmptyString(),
            new EndsWith('.sql'),
        ]);

        $this->stubFileGenerator->generateRawFile(
            stubFile: $stubFile,
            targetPath: $targetPath,
            shouldOverride: $this->askForOverride($targetPath),
            replacements: [
                'DummyTableName' => $tableName,
            ],
        );

        return $targetPath;
    }

    private function generateClassFile(string $filename, StubFile $stubFile): string
    {
        $tableName = str($filename)
            ->snake()
            ->stripStart('create')
            ->stripEnd('table')
            ->stripStart('_')
            ->stripEnd('_')
            ->toString();

        $filename = str($filename)
            ->afterLast(['\\', '/'])
            ->start('Create')
            ->finish('Table')
            ->when(
                condition: Str\contains($filename, ['\\', '/']),
                callback: fn ($path) => $path->prepend(Str\before_last($filename, ['\\', '/']), '/'),
            )
            ->toString();

        $targetPath = $this->promptTargetPath($this->getSuggestedPath($filename));

        $this->stubFileGenerator->generateClassFile(
            stubFile: $stubFile,
            targetPath: $targetPath,
            shouldOverride: $this->askForOverride($targetPath),
            replacements: [
                'dummy-date' => date('Y-m-d'),
                'dummy-table-name' => $tableName,
            ],
            manipulations: [
                static fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
            ],
        );

        return $targetPath;
    }
}
