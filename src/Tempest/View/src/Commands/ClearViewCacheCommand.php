<?php

declare(strict_types=1);

namespace Tempest\View\Commands;

use Tempest\Cache\CouldNotClearCache;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\View\ViewCache;

final readonly class ClearViewCacheCommand
{
    use HasConsole;

    public function __construct(
        private Container $container,
    ) {
    }

    #[ConsoleCommand(name: 'view:clear', description: 'Clears the view cache')]
    public function __invoke(): void
    {
        $this->console->header('Clearing the view cache');

        try {
            $this->container->get(ViewCache::class)->clear();
            $value = "<style='bold fg-green'>CLEARED</style>";
        } catch (CouldNotClearCache) {
            $value = "<style='bold fg-red'>FAILEd</style>";
        }

        $this->console->keyValue(key: ViewCache::class, value: $value);
    }
}
