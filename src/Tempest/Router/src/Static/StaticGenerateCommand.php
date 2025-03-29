<?php

declare(strict_types=1);

namespace Tempest\Router\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\EventBus\EventBus;
use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\Router\DataProvider;
use Tempest\Router\GenericRequest;
use Tempest\Router\Router;
use Tempest\Router\Static\Exceptions\InvalidStatusCodeException;
use Tempest\Router\Static\Exceptions\NoTextualBodyException;
use Tempest\View\Exceptions\ViewCompilationError;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use Tempest\Vite\Exceptions\ManifestNotFoundException;
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
        private EventBus $eventBus,
    ) {}

    #[ConsoleCommand(name: 'static:generate', description: 'Compiles static pages')]
    public function __invoke(
        ?string $filter = null,
        #[ConsoleArgument(aliases: ['v'])]
        bool $verbose = false,
    ): ExitCode {
        $publicPath = path($this->kernel->root, 'public');

        $generated = 0;
        $failures = 0;

        $this->console->header('Generating static pages');

        $this->eventBus->listen(StaticPageGenerated::class, function (StaticPageGenerated $event) use (&$generated): void {
            $generated++;
            $this->keyValue("<style='fg-gray'>{$event->uri}</style>", "<style='fg-green'>{$event->path}</style>");
        });

        $this->eventBus->listen(StaticPageGenerationFailed::class, function (StaticPageGenerationFailed $event) use (&$failures, $verbose): void {
            $failures++;

            match (true) {
                $event->exception instanceof InvalidStatusCodeException => $this->keyValue(
                    "<style='fg-gray'>{$event->path}</style>",
                    "<style='fg-red'>HTTP {$event->exception->status->value}</style>",
                ),
                $event->exception instanceof NoTextualBodyException => $this->keyValue(
                    "<style='fg-gray'>{$event->path}</style>",
                    "<style='fg-red'>NO CONTENT</style>",
                ),
                $verbose === true => $this->error("Failed to generate static page: {$event->exception->getMessage()}"),
                default => $this->keyValue("<style='fg-gray'>{$event->path}</style>", "<style='fg-red'>FAILED</style>"),
            };
        });

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
                        throw new InvalidStatusCodeException($uri, $response->status);
                    }

                    $body = $response->body;

                    $content = ($body instanceof View)
                        ? $this->viewRenderer->render($body)
                        : $body;

                    if (! is_string($content)) {
                        throw new NoTextualBodyException($uri);
                    }

                    $directory = $file->dirname();

                    if (! $directory->isDirectory()) {
                        mkdir($directory->toString(), recursive: true);
                    }

                    file_put_contents($file->toString(), $content);

                    $this->eventBus->dispatch(new StaticPageGenerated($uri, $file->toString(), $content));
                } catch (Throwable $exception) {
                    ob_get_clean();

                    if ($exception instanceof ViewCompilationError && $exception->getPrevious() instanceof ManifestNotFoundException) {
                        $this->error('Run <code>vite build</code> first.');
                        return ExitCode::ERROR;
                    }

                    $this->eventBus->dispatch(new StaticPageGenerationFailed($uri, $exception));

                    continue;
                }
            }
        }

        if ($failures) {
            $this->keyValue('Failures', "<style='fg-red'>{$failures}</style>");
        }

        $this->keyValue('Static pages generated', "<style='fg-green'>{$generated}</style>");

        return $failures > 0
            ? ExitCode::ERROR
            : ExitCode::SUCCESS;
    }
}
