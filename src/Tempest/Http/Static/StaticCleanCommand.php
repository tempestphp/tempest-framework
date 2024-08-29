<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Framework\Application\AppConfig;
use Tempest\Http\DataProvider;
use Tempest\Http\Router;
use function Tempest\path;
use function Tempest\uri;
use Tempest\View\ViewRenderer;

final readonly class StaticCleanCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
        private Container $container,
        private StaticRouteConfig $staticRouteConfig,
        private Router $router,
        private ViewRenderer $viewRenderer,
    ) {
    }

    #[ConsoleCommand(
        name: 'static:clean'
    )]
    public function __invoke(): void
    {
        $publicPath = path($this->appConfig->root, 'public');

        foreach ($this->staticRouteConfig->staticRoutes as $staticRoute) {
            /** @var DataProvider $dataProvider */
            $dataProvider = $this->container->get($staticRoute->dataProviderClass ?? GenericDataProvider::class);

            foreach ($dataProvider->provide() as $params) {
                $uri = uri([
                    $staticRoute->handler->getDeclaringClass()->getName(),
                    $staticRoute->handler->getName(),
                ], ...$params);

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
