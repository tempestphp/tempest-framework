<?php

declare(strict_types=1);

namespace Tempest\Cache\Commands;

use Tempest\Cache\Cache;
use Tempest\Cache\Config\CacheConfig;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Cache\GenericCache;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Support\Str;
use UnitEnum;

use function Tempest\Support\arr;

final readonly class CacheStatusCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private Container $container,
    ) {}

    #[ConsoleCommand(name: 'cache:status', description: 'Shows which caches are enabled')]
    public function __invoke(): void
    {
        if (! ($this->container instanceof GenericContainer)) {
            $this->console->error('Clearing caches is only available when using the default container.');
            return;
        }

        $caches = arr($this->container->getSingletons(CacheConfig::class))
            ->map(fn ($_, string $key) => $this->container->get(Cache::class, $key === CacheConfig::class ? null : Str\after_last($key, '#')))
            ->values();

        $this->console->header('Cache status');
        $this->console->keyValue('Total caches', (string) count($caches));

        /** @var Cache $cache */
        foreach ($caches as $cache) {
            $this->console->keyValue(
                key: $this->getCacheName($cache),
                value: match ($cache->enabled) {
                    true => '<style="bold fg-green">ENABLED</style>',
                    false => '<style="bold fg-red">DISABLED</style>',
                },
            );
        }
    }

    private function getCacheName(Cache $cache): string
    {
        if (! ($cache instanceof GenericCache)) {
            return $cache::class;
        }

        $tag = $cache->cacheConfig->tag;

        if ($tag instanceof UnitEnum) {
            return $tag->name;
        }

        return $tag ?? 'default';
    }
}
