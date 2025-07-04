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
    ): void
    {
        foreach ($this->seederConfig->seeders as $seederClass) {
            /** @var \Tempest\Database\DatabaseSeeder $seeder */
            $seeder = $this->container->get($seederClass);
            $seeder->run($database);

            $this->console->keyValue(
                key: "<style='fg-gray'>{$seederClass}</style>",
                value: "<style='fg-green'>SEEDED</style>",
            );
        }
    }
}