<?php

namespace Tempest\Http\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Framework\Application\AppConfig;
use Tempest\Http\GenericRequest;
use Tempest\Http\Mappers\RequestToPsrRequestMapper;
use Tempest\Http\Method;
use Tempest\Http\RouteConfig;
use Tempest\Http\Router;
use Tempest\Http\Status;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use function Tempest\map;
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
        private Router $router,
        private ViewRenderer $viewRenderer,
    ) {}

    #[ConsoleCommand(
        name: 'static:clean'
    )]
    public function __invoke(): void
    {
        $publicPath = path($this->appConfig->root, 'public');

        foreach ($this->staticPageConfig->staticPages as $staticPage) {
            /** @var \Tempest\Http\DataProvider $dataProvider */
            $dataProvider = $this->container->get($staticPage->dataProviderClass ?? GenericDataProvider::class);

            foreach ($dataProvider->provide() as $params) {
                $uri = uri([
                    $staticPage->handler->getDeclaringClass()->getName(),
                    $staticPage->handler->getName(),
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