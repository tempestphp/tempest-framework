<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\AppConfig;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutputBuilder;

final readonly class DiscoveryStatusCommand
{
    public function __construct(
        private AppConfig $appConfig,
        private ConsoleOutputBuilder $outputBuilder,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:status',
        description: 'List all discovery locations and discovery classes'
    )]
    public function __invoke(): void
    {
        $this->outputBuilder
            ->header("Tempest")
            ->warning('Discovery status')
            ->blank()
            ->info('Loaded discovery classes')
            ->add($this->appConfig->discoveryClasses)
            ->blank()
            ->info('Folders included in Tempest')
            ->add(array_column($this->appConfig->discoveryLocations, 'path'))
            ->blank()
            ->write();
    }
}
