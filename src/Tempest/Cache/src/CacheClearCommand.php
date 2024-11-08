<?php

declare(strict_types=1);

namespace Tempest\Cache;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;

final readonly class CacheClearCommand
{
    use HasConsole;

    public function __construct(
        private CacheConfig $cacheConfig,
        private Container $container,
    ) {
    }

    #[ConsoleCommand(name: 'cache:clear', aliases: ['cc'])]
    public function __invoke(bool $all = false): void
    {
        $caches = $this->cacheConfig->caches;

        if ($all === false) {
            $caches = $this->ask(
                question: "Which caches do you want to clear?",
                options: $this->cacheConfig->caches,
                multiple: true,
            );
        }

        foreach ($caches as $cacheClass) {
            /** @var Cache $cache */
            $cache = $this->container->get($cacheClass);

            $cache->clear();

            $this->writeln("<em>{$cacheClass}</em> cleared successfully");
        }

        $this->success('Done');
    }
}
