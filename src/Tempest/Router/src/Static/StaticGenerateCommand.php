<?php

declare(strict_types=1);

namespace Tempest\Router\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Router\DataProvider;
use Tempest\Router\GenericRequest;
use Tempest\Router\Router;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use Throwable;

use function Tempest\Support\path;
use function Tempest\uri;

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
    ) {}

    #[ConsoleCommand(
        name: 'static:generate',
        description: 'Compiles static pages',
    )]
    public function __invoke(?string $filter = null): void
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
                    : ($uri . '/index.html');

                if ($filter !== null && $uri !== $filter) {
                    continue;
                }

                $file = path($publicPath, $fileName);

                try {
                    $response = $this->router->dispatch(
                        new GenericRequest(
                            method: Method::GET,
                            uri: $uri,
                        ),
                    );

                    if ($response->status !== Status::OK) {
                        $this->writeln("- <error>{$uri}</error> > {$response->status->value}");

                        continue;
                    }

                    $body = $response->body;

                    $content = ($body instanceof View)
                        ? $this->viewRenderer->render($body)
                        : $body;

                    if (! is_string($content)) {
                        $this->error("<u>{$uri}</u> No textual body");

                        continue;
                    }

                    $directory = $file->dirname();

                    if (! is_dir($directory)) {
                        mkdir($directory, recursive: true);
                    }

                    file_put_contents($file->path(), $content);

                    $this->writeln("- <em>{$uri}</em> > <u>{$file}</u>");
                } catch (Throwable $e) {
                    $this->error("<u>{$uri}</u> {$e->getMessage()}");

                    ob_get_clean();

                    continue;
                }
            }
        }

        $this->success('Done');
    }
}
