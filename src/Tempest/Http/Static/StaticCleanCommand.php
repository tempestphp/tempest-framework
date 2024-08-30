<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Framework\Application\AppConfig;
use Tempest\Http\DataProvider;
use function Tempest\path;
use function Tempest\uri;

final readonly class StaticCleanCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
        private Container $container,
        private StaticPageConfig $staticPageConfig,
    ) {
    }

    #[ConsoleCommand(
        name: 'static:clean'
    )]
    public function __invoke(): void
    {
        $publicPath = path($this->appConfig->root, 'public');

        foreach ($this->staticPageConfig->staticPages as $staticPage) {
            /** @var DataProvider $dataProvider */
            $dataProvider = $this->container->get($staticPage->dataProviderClass ?? GenericDataProvider::class);

            foreach ($dataProvider->provide() as $params) {
                $uri = uri($staticPage->handler, ...$params);

                $file = path($publicPath, $uri . '.html');

                if (! file_exists($file)) {
                    continue;
                }

                unlink($file);

                $this->writeln("- <u>{$file}</u> removed");
            }
        }

        $this->success('Done');
    }
}
