<?php

declare(strict_types=1);

namespace Tempest\Router\Static;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\Kernel;
use Tempest\EventBus\EventBus;
use Tempest\Http\GenericRequest;
use Tempest\Http\Method;
use Tempest\Http\Status;
use Tempest\HttpClient\HttpClient;
use Tempest\Intl;
use Tempest\Router\DataProvider;
use Tempest\Router\RouteConfig;
use Tempest\Router\Router;
use Tempest\Router\Static\Exceptions\DeadLinksDetectedException;
use Tempest\Router\Static\Exceptions\InvalidStatusCodeException;
use Tempest\Router\Static\Exceptions\NoTextualBodyException;
use Tempest\Support\Arr;
use Tempest\Support\Filesystem;
use Tempest\Support\Regex;
use Tempest\Support\Str;
use Tempest\View\Exceptions\ViewCompilationFailed;
use Tempest\View\View;
use Tempest\View\ViewRenderer;
use Tempest\Vite\Exceptions\ManifestWasNotFound;
use Throwable;

use function Tempest\Support\path;
use function Tempest\Support\str;
use function Tempest\uri;

final class StaticGenerateCommand
{
    use HasConsole;

    private array $verifiedLinks = [];

    public function __construct(
        private readonly AppConfig $appConfig,
        private readonly RouteConfig $routeConfig,
        private readonly Console $console,
        private readonly Kernel $kernel,
        private readonly Container $container,
        private readonly StaticPageConfig $staticPageConfig,
        private readonly Router $router,
        private readonly ViewRenderer $viewRenderer,
        private readonly EventBus $eventBus,
        private readonly HttpClient $httpClient,
    ) {}

    #[ConsoleCommand(name: 'static:generate', description: 'Compiles static pages')]
    public function __invoke(
        ?string $filter = null,
        bool $allowDeadLinks = false,
        bool $allowExternalDeadLinks = true,
        #[ConsoleArgument(aliases: ['v'])]
        bool $verbose = false,
    ): ExitCode {
        $publicPath = path($this->kernel->root, 'public');

        $generated = 0;
        $failures = 0;
        $deadlinks = [];

        $this->console->header('Generating static pages');

        $this->eventBus->listen(function (StaticPageGenerated $event) use (&$generated): void {
            $generated++;
            $this->keyValue("<style='fg-gray'>{$event->uri}</style>", "<style='fg-green'>{$event->path}</style>");
        });

        $this->eventBus->listen(function (StaticPageGenerationFailed $event) use (&$failures, $verbose): void {
            $failures++;

            match (true) {
                $event->exception instanceof DeadLinksDetectedException => $this->keyValue(
                    "<style='fg-gray'>{$event->path}</style>",
                    sprintf("<style='fg-red'>%s DEAD %s</style>", count($event->exception->links), Intl\pluralize('LINK', count($event->exception->links))),
                ),
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

        $this->routeConfig->throwHttpExceptions = false;

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

                    if (! $allowDeadLinks && count($links = $this->detectDeadLinks($uri, $content, checkExternal: ! $allowExternalDeadLinks)) > 0) {
                        $deadlinks[$uri] = $links;
                        throw new DeadLinksDetectedException($uri, $links);
                    }

                    Filesystem\write_file($file->toString(), $content);

                    $this->eventBus->dispatch(new StaticPageGenerated($uri, $file->toString(), $content));
                } catch (Throwable $exception) {
                    if (ob_get_contents()) {
                        ob_clean();
                    }

                    if ($exception instanceof ViewCompilationFailed && $exception->getPrevious() instanceof ManifestWasNotFound) {
                        $this->error("A Vite build is needed for [{$uri}]. Run <code>vite build</code> first.");
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

        if ($deadlinks) {
            $this->console->header('Dead links');

            foreach ($deadlinks as $uri => $links) {
                foreach ($links as $link) {
                    $this->keyValue("<style='fg-gray'>{$uri}</style>", "<style='fg-red'>{$link}</style>");
                }
            }
        }

        return $failures > 0 || count($deadlinks) > 0
            ? ExitCode::ERROR
            : ExitCode::SUCCESS;
    }

    private function detectDeadLinks(string $uri, string $html, bool $checkExternal = false): array
    {
        $deadlinks = [];
        $links = Regex\get_all_matches($html, '/<a\s+(?<ignore>ssg-ignore)?[^>]*href=["\'](?<url>[^"\']+)["\'][^>]*>/i', matches: ['url', 'ignore']);

        foreach ($links as ['url' => $link, 'ignore' => $ignore]) {
            // Links can be ignored with the ssg-ignore attribute
            if ($ignore ?: false) {
                continue;
            }

            // Check anchors (#)
            if (Str\starts_with($link, '#')) {
                if (! Regex\matches($html, "/id=\"" . preg_quote(Str\strip_start($link, '#'), '/') . "\"/")) {
                    $deadlinks[] = $link;
                }

                continue;
            }

            // Resolve relative links (../ or ./)
            if (Str\starts_with($link, ['../', './'])) {
                $link = $this->resolveRelativeLink($uri, $link);
            }

            // Don't ping the same link multiple times
            if (in_array($link, $this->verifiedLinks, strict: true)) {
                continue;
            }

            $this->verifiedLinks[] = $link;

            // Check internal links with router (/ or same base uri)
            if (Str\starts_with($link, '/') || Str\starts_with($this->getLinkWithoutProtocol($link), $this->getLinkWithoutProtocol($this->appConfig->baseUri))) {
                do {
                    $target ??= match (true) {
                        ! Str\starts_with($link, '/') => str($link)->stripStart($this->appConfig->baseUri)->finish('/')->toString(),
                        default => $link,
                    };

                    $response = $this->router->dispatch(new GenericRequest(
                        method: Method::GET,
                        uri: $target,
                    ));

                    if ($response->status->isRedirect()) {
                        $target = Arr\first($response->getHeader('Location')->values);
                    }
                } while ($response->status->isRedirect());

                if ($response->status->isClientError() || $response->status->isServerError()) {
                    $deadlinks[] = $link;
                }

                continue;
            }

            if (! $checkExternal) {
                continue;
            }

            if (Str\starts_with($link, 'http')) {
                $response = $this->httpClient->get($link);

                if ($response->status->isClientError() || $response->status->isServerError()) {
                    $deadlinks[] = $link;
                }

                continue;
            }

            // If we reach this, there is an unknown kind of link.
        }

        return $deadlinks;
    }

    /**
     * Resolves paths starting with ./ or ../ to a canonical URI.
     */
    private function resolveRelativeLink(string $basePath, string $relativePath): string
    {
        $basePath = Str\strip_end($basePath, '/');

        if (Str\starts_with($relativePath, ['../', './'])) {
            $baseParts = explode('/', $basePath);
            $relativeParts = explode('/', $relativePath);

            array_pop($baseParts);

            foreach ($relativeParts as $part) {
                if ($part === '..') {
                    array_pop($baseParts);
                } elseif ($part !== '.') {
                    $baseParts[] = $part;
                }
            }

            return implode('/', $baseParts);
        }

        return $basePath . '/' . Str\strip_start($relativePath, './');
    }

    private function getLinkWithoutProtocol(string $link): string
    {
        return Str\strip_start($link, ['https://', 'http://']);
    }
}
