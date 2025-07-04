<?php

namespace Tempest\Framework\Commands;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use Tempest\Database\Config\SeederConfig;

final class DatabaseSeedCommand
{
    use HasConsole;

    public function __construct(
        private readonly Container $container,
        private readonly SeederConfig $seederConfig,
    ) {}

    #[ConsoleCommand(
        aliases: ['db:seed'],
        middleware: [ForceMiddleware::class, CautionMiddleware::class],
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Use a specific database.')]
        ?string $database = null,
        #[ConsoleArgument(description: 'Run all database seeders')]
        bool $all = false,
        #[ConsoleArgument(description: 'Select one specific seeder to run')]
        ?string $seeder = null,
    ): void {
        if ($seeder !== null) {
            $this->runSeeder($seeder, $database);
            return;
        }

        if (count($this->seederConfig->seeders) === 1) {
            $this->runSeeder($this->seederConfig->seeders[0], $database);
            return;
        }

        if ($all) {
            $seedersToRun = $this->seederConfig->seeders;
        } else {
            $seedersToRun = $this->ask(
                question: 'Which seeders do you want to run?',
                options: $this->seederConfig->seeders,
                multiple: true,
            );
        }

        foreach ($seedersToRun as $seederClass) {
            $this->runSeeder($seederClass, $database);
        }
    }

    private function runSeeder(string $seederClass, ?string $database): void
    {
        /** @var \Tempest\Database\DatabaseSeeder $seeder */
        $seeder = $this->container->get($seederClass);
        $seeder->run($database);

        $this->console->keyValue(
            key: "<style='fg-gray'>{$seederClass}</style>",
            value: "<style='fg-green'>SEEDED</style>",
        );
    }
}
