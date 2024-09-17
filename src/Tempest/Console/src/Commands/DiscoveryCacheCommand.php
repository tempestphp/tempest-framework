<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\Kernel;

final readonly class DiscoveryCacheCommand
{
    use HasConsole;

    public function __construct(
        private Container $container,
        private Console $console,
        private Kernel $kernel,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:cache',
        description: 'Generate and store the discovery cache',
        aliases: ['discovery:store', 'discovery:warm'],
    )]
    public function __invoke(): void
    {
        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            $discovery->storeCache();

            $this->writeln(sprintf(
                '<em>%s</em> cached successful',
                $discoveryClass,
            ));
        }

        $this->console->success('Done');
    }
}
