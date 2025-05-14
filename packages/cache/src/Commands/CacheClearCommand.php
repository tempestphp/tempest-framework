<?php

declare(strict_types=1);

namespace Tempest\Cache\Commands;

use Tempest\Cache\Cache;
use Tempest\Cache\Config\CacheConfig;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Middleware\CautionMiddleware;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Support\Str;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final readonly class CacheClearCommand
{
    use HasConsole;

    public function __construct(
        private Cache $cache,
        private Container $container,
    ) {}

    #[ConsoleCommand(name: 'cache:clear', description: 'Clears all or specified caches', middleware: [CautionMiddleware::class])]
    public function __invoke(
        #[ConsoleArgument(description: 'Name of the tagged cache to clear')]
        ?string $tag = null,
        #[ConsoleCommand(description: 'Whether to clear all caches')]
        bool $all = false,
    ): void {
        if (! ($this->container instanceof GenericContainer)) {
            $this->console->error('Clearing caches is only available when using the default container.');
            return;
        }

        if ($tag && $all) {
            $this->console->error('You cannot specify both a tag and clear all caches.');
            return;
        }

        $caches = arr($this->container->getSingletons(CacheConfig::class))
            ->map(fn ($_, string $key) => $key === CacheConfig::class ? 'default' : Str\after_last($key, '#'))
            ->filter(fn ($_, string $key) => in_array($tag, [null, 'default'], strict: true) ? true : str($key)->afterLast('#')->equals($tag))
            ->values();

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

        $this->console->header('Clearing caches');

        foreach ($caches as $tag) {
            $cache = $this->container->get(Cache::class, $tag === 'default' ? null : $tag);
            $cache->clear();

            $this->console->keyValue(
                key: $tag,
                value: "<style='bold fg-green'>CLEARED</style>",
            );
        }
    }
}
