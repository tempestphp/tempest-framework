<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Closure;
use ErrorException;
use Stringable;
use Tempest\Container\Container;
use Tempest\Core\Environment;
use Tempest\Support\Filesystem;
use Tempest\Support\Html\HtmlString;
use Tempest\View\Attributes\AttributeFactory;
use Tempest\View\Elements\ElementFactory;
use Tempest\View\Exceptions\ViewCompilationFailed;
use Tempest\View\Exceptions\ViewVariableWasReserved;
use Tempest\View\GenericView;
use Tempest\View\Parser\TempestViewCompiler;
use Tempest\View\View;
use Tempest\View\ViewCache;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;
use Throwable;

final class TempestViewRenderer implements ViewRenderer
{
    private ?View $currentView = null;

    /** @var array<string, Closure> */
    private array $includedViewComponents = [];

    public function __construct(
        private readonly TempestViewCompiler $compiler,
        private readonly ViewCache $viewCache,
        private readonly ViewConfig $viewConfig,
        private readonly ?Container $container,
    ) {}

    public static function make(
        ?ViewConfig $viewConfig = null,
        ?ViewCache $viewCache = null,
        Environment $environment = Environment::PRODUCTION,
    ): self {
        $viewConfig ??= new ViewConfig();
        $viewCache ??= ViewCache::create(enabled: false);

        $elementFactory = new ElementFactory(
            $viewConfig,
            $environment,
            $viewCache,
        );

        $compiler = new TempestViewCompiler(
            elementFactory: $elementFactory,
            attributeFactory: new AttributeFactory(),
        );

        $elementFactory->setViewCompiler($compiler);

        return new self(
            compiler: $compiler,
            viewCache: $viewCache,
            viewConfig: $viewConfig,
            container: null,
        );
    }

    public function __get(string $name): mixed
    {
        return $this->currentView?->get($name);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->currentView?->{$name}(...$arguments);
    }

    public function render(string|View $view): string
    {
        $view = is_string($view) ? new GenericView($view) : $view;

        $this->validateView($view);

        $compiledView = null;

        $path = $this->viewCache->getCachedViewPath(
            path: $view->path,
            compiledView: function () use (&$compiledView, $view): string {
                $compiledView = $this->compiler->compileWithSourceMap($view);

                return $compiledView->content;
            },
        );

        if ($compiledView !== null) {
            $this->viewCache->saveSourceMap($path, $compiledView->sourcePath, $compiledView->lineMap);
        }

        $view = $this->processView($view);

        return $this->renderCompiled($view, $path);
    }

    public function includeViewComponent(string $path): Closure
    {
        /** @var Closure */
        return $this->includedViewComponents[$path] ??= include $path;
    }

    private function processView(View $view): View
    {
        foreach ($this->viewConfig->viewProcessors as $viewProcessorClass) {
            if ($this->container) {
                /**  @var \Tempest\View\ViewProcessor $viewProcessor */
                $viewProcessor = $this->container->get($viewProcessorClass);
            } else {
                $viewProcessor = new $viewProcessorClass();
            }

            $view = $viewProcessor->process($view);
        }

        return $view;
    }

    private function renderCompiled(View $_view, string $_path): string
    {
        $this->currentView = $_view;

        ob_start();

        // Extract data from view into local variables so that they can be accessed directly
        $_data = $_view->data;

        extract($_data, flags: EXTR_SKIP);

        set_error_handler(static function (int $code, string $message, string $filename, int $line): bool {
            throw new ErrorException(
                message: $message,
                code: $code,
                filename: $filename,
                line: $line,
            );
        });

        try {
            include $_path;
        } catch (Throwable $throwable) {
            ob_end_clean(); // clean buffer before rendering exception

            $sourceLocation = $this->resolveSourceLocationFromThrowable($throwable, $_path);

            throw new ViewCompilationFailed(
                path: $_path,
                content: Filesystem\is_file($_path) ? Filesystem\read_file($_path) : '',
                previous: $throwable,
                sourcePath: $sourceLocation['path'] ?? null,
                sourceLine: $sourceLocation['line'] ?? null,
            );
        } finally {
            restore_error_handler();
        }

        $this->currentView = null;

        return trim(ob_get_clean());
    }

    public function escape(null|string|HtmlString|Stringable $value): string
    {
        if ($value instanceof HtmlString) {
            return (string) $value;
        }

        return htmlentities(
            string: (string) $value,
            flags: ENT_QUOTES | ENT_SUBSTITUTE,
            encoding: 'UTF-8',
        );
    }

    private function validateView(View $view): void
    {
        $data = $view->data;

        if (array_key_exists('slots', $data)) {
            throw new ViewVariableWasReserved('slots');
        }
    }

    /** @return array{path: string, line: int}|null */
    private function resolveSourceLocation(string $compiledPath, int $compiledLine): ?array
    {
        $sourceMap = $this->viewCache->getSourceMap($compiledPath);

        if ($sourceMap === null) {
            return null;
        }

        $sourceLocation = $this->resolveSourceLine(
            $compiledLine,
            $sourceMap['sourcePath'],
            $sourceMap['lineMap'],
        );

        if ($sourceLocation === null) {
            return null;
        }

        return [
            'path' => $sourceLocation['path'],
            'line' => $sourceLocation['line'],
        ];
    }

    /** @return array{path: string, line: int}|null */
    private function resolveSourceLocationFromThrowable(Throwable $throwable, string $compiledPath): ?array
    {
        $sourceLocation = $this->resolveSourceLocationForFrame($throwable->getFile(), $throwable->getLine());

        if ($sourceLocation !== null) {
            return $sourceLocation;
        }

        foreach ($throwable->getTrace() as $frame) {
            $framePath = $frame['file'] ?? null;
            $frameLine = $frame['line'] ?? null;

            if (! is_string($framePath) || ! is_int($frameLine)) {
                continue;
            }

            $sourceLocation = $this->resolveSourceLocationForFrame($framePath, $frameLine);

            if ($sourceLocation !== null) {
                return $sourceLocation;
            }
        }

        return $this->resolveSourceLocation($compiledPath, 1);
    }

    /** @return array{path: string, line: int}|null */
    private function resolveSourceLocationForFrame(string $compiledPath, int $compiledLine): ?array
    {
        $sourceLocation = $this->resolveSourceLocation($compiledPath, $compiledLine);

        if ($sourceLocation === null) {
            return null;
        }

        return [
            'path' => $sourceLocation['path'],
            'line' => $this->refineSourceLine(
                compiledPath: $compiledPath,
                compiledLine: $compiledLine,
                sourcePath: $sourceLocation['path'],
                fallbackLine: $sourceLocation['line'],
            ),
        ];
    }

    private function refineSourceLine(string $compiledPath, int $compiledLine, string $sourcePath, int $fallbackLine): int
    {
        if (! Filesystem\is_file($compiledPath) || ! Filesystem\is_file($sourcePath)) {
            return $fallbackLine;
        }

        try {
            $compiledLines = preg_split('/\R/', Filesystem\read_file($compiledPath));
            $sourceLines = preg_split('/\R/', Filesystem\read_file($sourcePath));
        } catch (Throwable) {
            return $fallbackLine;
        }

        if (! is_array($compiledLines) || ! is_array($sourceLines)) {
            return $fallbackLine;
        }

        $compiledLineContent = $compiledLines[$compiledLine - 1] ?? null;

        if (! is_string($compiledLineContent)) {
            return $fallbackLine;
        }

        foreach ($this->extractSourceNeedles($compiledLineContent) as $needle) {
            $matches = $this->findMatchingSourceLines($sourceLines, $needle);

            if ($matches === []) {
                continue;
            }

            if (count($matches) === 1) {
                return $matches[0];
            }

            return $this->closestLineToFallback($matches, $fallbackLine);
        }

        return $fallbackLine;
    }

    /** @return list<string> */
    private function extractSourceNeedles(string $compiledLine): array
    {
        $needles = [];

        if (preg_match('/value:\s*(?<expression>.+?)\s*\?\?\s*null/', $compiledLine, $matches) === 1) {
            $needles[] = $matches['expression'];
        }

        if (preg_match('/\$this->escape\(\s*(?<expression>.+?)\s*\);/', $compiledLine, $matches) === 1) {
            $needles[] = $matches['expression'];
        }

        if (preg_match('/<\?=\s*(?<expression>.+?)\s*\?>/', $compiledLine, $matches) === 1) {
            $needles[] = $matches['expression'];
        }

        if (preg_match('/\b(?:if|elseif)\s*\((?<expression>.+?)\)\s*:/', $compiledLine, $matches) === 1) {
            $needles[] = $matches['expression'];
        }

        if (preg_match('/\bforeach\s*\((?<expression>.+?)\)\s*:/', $compiledLine, $matches) === 1) {
            $needles[] = $matches['expression'];
        }

        $needles[] = $compiledLine;

        $normalized = [];

        foreach ($needles as $needle) {
            $needle = trim($needle);

            if ($needle === '') {
                continue;
            }

            $normalized[$needle] = $needle;
        }

        return array_values($normalized);
    }

    /** @param list<string> $sourceLines */
    private function findMatchingSourceLines(array $sourceLines, string $needle): array
    {
        $normalizedNeedle = $this->normalizeSearchString($needle);

        if ($normalizedNeedle === '') {
            return [];
        }

        $matches = [];

        foreach ($sourceLines as $index => $sourceLine) {
            if (! str_contains($this->normalizeSearchString($sourceLine), $normalizedNeedle)) {
                continue;
            }

            $matches[] = $index + 1;
        }

        return $matches;
    }

    private function normalizeSearchString(string $value): string
    {
        return preg_replace('/\s+/', '', $value) ?? '';
    }

    /** @param list<int> $lines */
    private function closestLineToFallback(array $lines, int $fallbackLine): int
    {
        $closestLine = $fallbackLine;
        $closestDistance = null;

        foreach ($lines as $line) {
            $distance = abs($line - $fallbackLine);

            if ($closestDistance !== null && $distance >= $closestDistance) {
                continue;
            }

            $closestLine = $line;
            $closestDistance = $distance;
        }

        return $closestLine;
    }

    /**
     * @param array<int, array{compiledStartLine: int, compiledEndLine: int, sourcePath?: string, sourceStartLine: int}> $lineMap
     * @return array{path: string, line: int}|null
     */
    private function resolveSourceLine(int $compiledLine, ?string $defaultSourcePath, array $lineMap): ?array
    {
        foreach ($lineMap as $entry) {
            if ($compiledLine < $entry['compiledStartLine'] || $compiledLine > $entry['compiledEndLine']) {
                continue;
            }

            $sourcePath = $entry['sourcePath'] ?? $defaultSourcePath;

            if (! is_string($sourcePath)) {
                return null;
            }

            return [
                'path' => $sourcePath,
                'line' => $entry['sourceStartLine'] + ($compiledLine - $entry['compiledStartLine']),
            ];
        }

        return null;
    }
}
