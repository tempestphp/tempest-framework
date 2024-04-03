<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\AppConfig;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutputBuilder;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;

final readonly class DiscoveryClearCommand
{
    public function __construct(
        private Container $container,
        private Console $console,
        private AppConfig $appConfig,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:clear',
        description: 'Clear all cached discovery files',
    )]
    public function __invoke(): void
    {
        $builder = ConsoleOutputBuilder::new()
            ->withDefaultBranding()
            ->warning('Clearing cached discovery files: ')
            ->write($this->console)
            ->blank()
            ->blank();

        foreach ($this->appConfig->discoveryClasses as $discoveryClass) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            $discovery->destroyCache();

            $builder->formatted(
                ConsoleOutputBuilder::new(" ")
                    ->info($discoveryClass)
                    ->add('cleared successful')
                    ->toString(),
            )
                ->blank()
                ->write($this->console);
        }

        $builder->blank()
            ->success('Discovery cache cleared')
            ->blank()
            ->write($this->console);
    }
}
