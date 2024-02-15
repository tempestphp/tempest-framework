<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Application\Kernel;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleStyle;
use Tempest\Interface\Console;
use Tempest\Interface\Container;

final readonly class CacheClearCommand
{
    public function __construct(
        private Container $container,
        private Kernel $kernel,
        private Console $console,
    ) {
    }

    #[ConsoleCommand(
        name: 'clear',
        description: 'Clear all cached discovery files',
    )]
    public function __invoke(): void
    {
        $discoveries = $this->kernel->getDiscoveries();

        foreach ($discoveries as $discoveryClass) {
            /** @var \Tempest\Interface\Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            $discovery->destroyCache();

            $this->console->success(implode('', [
                ConsoleStyle::FG_BLUE($discoveryClass),
                ' cleared successful',
            ]));
        }

        $this->console->writeln('Done');
    }
}
