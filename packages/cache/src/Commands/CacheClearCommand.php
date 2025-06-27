<?php

declare(strict_types=1);

namespace Tempest\Cache\Commands;

use Tempest\Cache\Cache;
use Tempest\Cache\Config\CacheConfig;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Console\Middleware\ForceMiddleware;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\ConfigCache;
use Tempest\Core\DiscoveryCache;
use Tempest\Icon\IconCache;
use Tempest\Support\Str;
use Tempest\View\ViewCache;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class CacheClearCommand
{
    use HasConsole;

    private const string DEFAULT_CACHE = 'default';

    public function __construct(
        private Cache $cache,
        private Container $container,
    ) {}

    #[ConsoleCommand(name: 'cache:clear', description: 'Clears all or specified caches', middleware: [ForceMiddleware::class, CautionMiddleware::class])]
    public function __invoke(
        #[ConsoleArgument(description: 'Name of the tagged cache to clear')]
        ?string $tag = null,
        #[ConsoleCommand(description: 'Whether to clear all caches')]
        bool $all = false,
        #[ConsoleCommand(description: 'Whether to clear internal caches')]
        bool $internal = false,
    ): void {
        if (! ($this->container instanceof GenericContainer)) {
            $this->console->error('Clearing caches is only available when using the default container.');
            return;
        }

        if ($internal) {
            $this->clearInternalCaches($all);
        } else {
            $this->clearUserCaches($tag, $all);
        }
    }

    private function clearInternalCaches(bool $all = false): void
    {
        $caches = [ConfigCache::class, ViewCache::class, IconCache::class, DiscoveryCache::class];

        if ($all === false && count($caches) > 1) {
            $caches = $this->ask(
                question: 'Which caches do you want to clear?',
                options: $caches,
                multiple: true,
            );
        }

        if (count($caches) === 0) {
            $this->console->info('No cache selected.');
            return;
        }

        $this->console->header('Internal caches');

        foreach ($caches as $cache) {
            $cache = $this->container->get($cache);
            $cache->clear();

            $this->console->keyValue(
                key: $cache::class,
                value: "<style='bold fg-green'>CLEARED</style>",
            );
        }
    }

    private function clearUserCaches(?string $tag = null, bool $all = false): void
    {
        if ($tag && $all) {
            $this->console->error('You cannot specify both a tag and clear all caches.');
            return;
        }

        /** @var GenericContainer $container */
        $container = $this->container;
        $cacheTags = arr($container->getSingletons(CacheConfig::class))
            ->map(fn ($_, string $key) => $key === CacheConfig::class ? self::DEFAULT_CACHE : Str\after_last($key, '#'))
            ->filter(fn ($_, string $key) => in_array($tag, [null, self::DEFAULT_CACHE], strict: true) ? true : str($key)->afterLast('#')->equals($tag))
            ->values();

        if ($all === false && count($cacheTags) > 1) {
            $cacheTags = $this->ask(
                question: 'Which caches do you want to clear?',
                options: $cacheTags,
                multiple: true,
            );
        }

        if (count($cacheTags) === 0) {
            $this->console->info('No cache selected.');
            return;
        }

        $this->console->header('User caches');

        foreach ($cacheTags as $tag) {
            $cache = $this->container->get(Cache::class, $tag === self::DEFAULT_CACHE ? null : $tag);
            $cache->clear();

            $this->console->keyValue(
                key: $tag,
                value: "<style='bold fg-green'>CLEARED</style>",
            );
        }
    }
}
