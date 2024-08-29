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

final readonly class StaticGenerateCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
        private Container $container,
        private StaticRouteConfig $staticRouteConfig,
        private Router $router,
        private ViewRenderer $viewRenderer,
    ) {}

    #[ConsoleCommand(
        name: 'static:generate'
    )]
    public function __invoke(): void
    {
        $publicPath = path($this->appConfig->root, 'public');

        foreach ($this->staticRouteConfig->staticRoutes as $staticRoute) {
            /** @var \Tempest\Http\DataProvider $dataProvider */
            $dataProvider = $this->container->get($staticRoute->dataProviderClass ?? GenericDataProvider::class);

            foreach ($dataProvider->provide() as $params) {
                $uri = uri([
                    $staticRoute->handler->getDeclaringClass()->getName(),
                    $staticRoute->handler->getName(),
                ], ...$params);

                $file = path($publicPath, $uri . '.html');

                $response = $this->router->dispatch(
                    map(new GenericRequest(
                        method: Method::GET,
                        uri: $uri,
                        
                    ))->with(RequestToPsrRequestMapper::class),
                );
                
                if ($response->getStatus() !== Status::OK) {
                    $this->writeln("- <error>{$uri}</error> > {$response->getStatus()->value}");
                    continue;
                }
                
                $body = $response->getBody();
                
                $content = $body instanceof View
                    ? $this->viewRenderer->render($body)
                    : $body;
                
                if (! is_string($content)) {
                    $this->writeln("- <error>{$uri}</error> > No textual body");
                    continue;
                }
                
                $directory = pathinfo($file, PATHINFO_DIRNAME);
                
                if (! is_dir($directory)) {
                    mkdir($directory, recursive: true);
                }
                
                file_put_contents($file, $content);

                $this->writeln("- <em>{$uri}</em> > <u>{$file}</u>");
            }
        }

        $this->success('Done');
    }
}