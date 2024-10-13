<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;

final readonly class CacheStatusCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private CacheConfig $cacheConfig,
        private Container $container,
    ) {
    }

    #[ConsoleCommand(name: 'cache:status', aliases: ['cs'])]
    public function __invoke(): void
    {
        $caches = $this->cacheConfig->caches;

        foreach ($caches as $cacheClass) {
            /** @var Cache $cache */
            $cache = $this->container->get($cacheClass);

            $reason = match($this->cacheConfig->enable) {
                true => ' (global CACHE = true)',
                false => ' (global CACHE = false)',
                null => '',
            };

            $this->writeln(sprintf(
                '<em>%s</em> %s%s',
                $cacheClass,
                $cache->isEnabled() ? '<success>enabled</success>' : '<error>disabled</error>',
                $reason
            ));
        }
    }
}
