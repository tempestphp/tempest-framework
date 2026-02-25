<?php

declare(strict_types=1);

namespace Tempest\Database\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\PublishesFiles;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Enums\MigrationType;
use Tempest\Database\Migrations\TableGuesser;
use Tempest\Database\Stubs\ObjectAlterMigrationStub;
use Tempest\Database\Stubs\ObjectMigrationStub;
use Tempest\Database\Stubs\UpAlterMigrationStub;
use Tempest\Database\Stubs\UpMigrationStub;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Generation\Php\ClassManipulator;
use Tempest\Generation\Php\DataObjects\StubFile;
use Tempest\Generation\Php\Exceptions\FileGenerationFailedException;
use Tempest\Validation\Rules\EndsWith;
use Tempest\Validation\Rules\IsNotEmptyString;

use function Tempest\Support\str;

final class MakeMigrationCommand
{
    use PublishesFiles;

    public function __construct(
        private readonly DatabaseConfig $databaseConfig,
    ) {}

    #[ConsoleCommand(
        name: 'make:migration',
        description: 'Creates a new migration file',
        aliases: ['migration:make', 'migration:create', 'create:migration'],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'The name of the migration')]
        ?string $name = null,
        #[ConsoleArgument(name: 'type', description: 'The type of the migration to create')]
        ?MigrationType $migrationType = null,
        #[ConsoleArgument(description: 'The table name', aliases: ['-t'])]
        ?string $table = null,
        #[ConsoleArgument(description: 'Create an alter migration', aliases: ['-a'])]
        ?bool $alter = null,
        #[ConsoleArgument(description: 'Skip interactive prompts, use defaults', aliases: ['-y'])]
        bool $yes = false,
    ): void {
        try {
            if ($yes && $name === null) {
                $this->error('The migration name is required when using -y.');

                return;
            }

            $migrationType ??= $yes
                ? MigrationType::OBJECT
                : $this->ask(
                    question: 'Choose the migration type',
                    options: MigrationType::class,
                    default: MigrationType::OBJECT,
                );

            $name ??= $this->ask(
                question: 'Enter the migration name',
                validation: [new IsNotEmptyString()],
            );

            $snakeName = str($name)->afterLast(['\\', '/'])->snake()->toString();
            $guess = TableGuesser::guess($snakeName);
            $guessedTable = $this->resolveTableName($guess->table ?? $snakeName);

            $table ??= $yes
                ? $guessedTable
                : $this->ask(
                    question: 'Enter the table name',
                    default: $guessedTable,
                    validation: [new IsNotEmptyString()],
                );

            $alter ??= $yes
                ? $guess !== null && ! $guess->isCreate
                : $this->confirm(
                    question: 'Is this an alteration?',
                    default: $guess !== null && ! $guess->isCreate,
                );

            $table = $this->resolveTableName($table);
            [$migrationName, $className] = $this->resolveNames($name, $alter);
            /** @var MigrationType $migrationType */
            $stub = $this->resolveStub($migrationType, $alter);

            $targetPath = match ($migrationType) {
                MigrationType::RAW => $this->generateRawFile($stub, $migrationName, $table, skipPrompts: $yes),
                default => $this->generateClassFile($name, $stub, $className, $migrationName, $table, skipPrompts: $yes),
            };

            $this->success(sprintf('Migration file successfully created at "%s".', $targetPath));
        } catch (FileGenerationFailedException $e) {
            $this->error($e->getMessage());
        }
    }

    private function resolveNames(string $name, bool $alter): array
    {
        $baseName = str($name)->afterLast(['\\', '/']);
        $className = $baseName->pascal()->toString();

        if ($alter) {
            return [$baseName->snake()->toString(), $className];
        }

        $entityName = str($className);

        if ($entityName->startsWith('Create')) {
            $entityName = $entityName->afterFirst('Create');
        }

        if ($entityName->endsWith('Table')) {
            $entityName = $entityName->beforeLast('Table');
        }

        $className = 'Create' . $entityName->pluralizeLastWord()->toString() . 'Table';

        return [str($className)->snake()->toString(), $className];
    }

    private function resolveTableName(string $name): string
    {
        $entityName = str($name)->singularizeLastWord()->pascal()->toString();

        return $this->databaseConfig->namingStrategy->getName($entityName);
    }

    private function resolveStub(MigrationType $type, bool $alter): StubFile
    {
        $source = match ($type) {
            MigrationType::RAW => $alter
                ? dirname(__DIR__) . '/Stubs/migration.alter.stub.sql'
                : dirname(__DIR__) . '/Stubs/migration.stub.sql',
            MigrationType::OBJECT => $alter ? ObjectAlterMigrationStub::class : ObjectMigrationStub::class,
            MigrationType::UP => $alter ? UpAlterMigrationStub::class : UpMigrationStub::class,
        };

        return StubFile::from($source);
    }

    private function generateRawFile(StubFile $stub, string $migrationName, string $tableName, bool $skipPrompts = false): string
    {
        $prefix = $this->databaseConfig->migrationNamingStrategy->generatePrefix();
        $suggestedPath = str($this->getSuggestedPath('Dummy'))
            ->replace(['Dummy', '.php'], ["{$prefix}_{$migrationName}", '.sql'])
            ->toString();

        $targetPath = $skipPrompts
            ? $suggestedPath
            : $this->promptTargetPath($suggestedPath, rules: [
                new IsNotEmptyString(),
                new EndsWith('.sql'),
            ]);

        $this->stubFileGenerator->generateRawFile(
            stubFile: $stub,
            targetPath: $targetPath,
            shouldOverride: $skipPrompts || $this->askForOverride($targetPath),
            replacements: ['DummyTableName' => $tableName],
        );

        return $targetPath;
    }

    private function generateClassFile(string $name, StubFile $stub, string $className, string $migrationName, string $tableName, bool $skipPrompts = false): string
    {
        $classFileName = str($className)
            ->when(
                condition: str($name)->contains(['\\', '/']),
                callback: fn ($path) => $path->prepend(str($name)->beforeLast(['\\', '/'])->toString(), '/'),
            )
            ->toString();

        $suggestedPath = $this->getSuggestedPath($classFileName);
        $targetPath = $skipPrompts ? $suggestedPath : $this->promptTargetPath($suggestedPath);

        $this->stubFileGenerator->generateClassFile(
            stubFile: $stub,
            targetPath: $targetPath,
            shouldOverride: $skipPrompts || $this->askForOverride($targetPath),
            replacements: [
                'dummy-date' => $this->databaseConfig->migrationNamingStrategy->generatePrefix(),
                'dummy-migration-name' => $migrationName,
                'dummy-table-name' => $tableName,
            ],
            manipulations: [
                static fn (ClassManipulator $class) => $class->removeClassAttribute(SkipDiscovery::class),
            ],
        );

        return $targetPath;
    }
}
