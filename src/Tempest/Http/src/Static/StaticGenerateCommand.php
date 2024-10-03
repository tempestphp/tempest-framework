<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\Http\DataProvider;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Router;
use Tempest\Http\Status;
use function Tempest\path;
use function Tempest\uri;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use Throwable;

final readonly class StaticGenerateCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private Kernel $kernel,
        private Container $container,
        private StaticPageConfig $staticPageConfig,
        private Router $router,
        private ViewRenderer $viewRenderer,
    ) {
    }

    #[ConsoleCommand(
        name: 'static:generate'
    )]
    public function __invoke(): void
    {
        $publicPath = path($this->kernel->root, 'public');

        foreach ($this->staticPageConfig->staticPages as $staticPage) {
            /** @var DataProvider $dataProvider */
            $dataProvider = $this->container->get($staticPage->dataProviderClass ?? GenericDataProvider::class);

            foreach ($dataProvider->provide() as $params) {
                if (! is_array($params)) {
                    $params = [$params];
                }

                $uri = parse_url(uri($staticPage->handler, ...$params), PHP_URL_PATH);

                $fileName = $uri === '/'
                    ? 'index.html'
                    : $uri . '/index.html';

                $file = path($publicPath, $fileName);

                try {
                    $response = $this->router->dispatch(
                        new GenericRequest(
                            method: Method::GET,
                            uri: $uri,
                        ),
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
                } catch (Throwable $e) {
                    $this->writeln("- <error>{$uri}</error> {$e->getMessage()}");

                    ob_get_clean();

                    continue;
                }
            }
        }

        $this->success('Done');
    }
}
