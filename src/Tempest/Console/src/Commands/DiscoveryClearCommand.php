<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Container\Container;
use Tempest\Core\Discovery;
use Tempest\Core\Kernel;

final readonly class DiscoveryClearCommand
{
    public function __construct(
        private Container $container,
        private Console $console,
        private Kernel $kernel,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:clear',
        description: 'Clear all cached discovery files',
        aliases: ['dc'],
    )]
    public function __invoke(): void
    {
        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            $discovery->destroyCache();

            $this->console->writeln(implode('', [
                "<em>{$discoveryClass}</em>",
                ' cleared successfully',
            ]));
        }

        $this->console->success('Done');
    }
}
