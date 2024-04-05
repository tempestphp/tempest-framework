<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\AppConfig;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutputBuilder;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;

final readonly class DiscoveryClearCommand
{
    public function __construct(
        private Container $container,
        private AppConfig $appConfig,
        private ConsoleOutputBuilder $builder,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:clear',
        description: 'Clear all cached discovery files',
    )]
    public function __invoke(): void
    {
        $this->builder
            ->header("Tempest")
            ->warning('Clearing cached discovery files:')
            ->write()
            ->blank()
            ->blank();

        foreach ($this->appConfig->discoveryClasses as $discoveryClass) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            $discovery->destroyCache();

            $this->builder->nest(function (ConsoleOutputBuilder $builder) use ($discoveryClass) {
                $builder->glueWith(" ")
                    ->info($discoveryClass)
                    ->add('cleared successful');
            })
                ->blank()
                ->write();
        }

        $this->builder->blank()
            ->success('Discovery cache cleared')
            ->blank()
            ->write();
    }
}
