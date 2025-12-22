<?php

namespace Tempest\Router\Exceptions;

use Tempest\Core\Kernel;
use Tempest\Core\ProvidesContext;
use Tempest\Debug\Stacktrace\CodeSnippet;
use Tempest\Debug\Stacktrace\Frame;
use Tempest\Debug\Stacktrace\Stacktrace;
use Tempest\Http\IsResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Support\Filesystem;
use Tempest\View\Exceptions\ViewCompilationFailed;
use Tempest\View\GenericView;
use Tempest\View\Renderers\TempestViewRenderer;
use Throwable;

use function Tempest\Mapper\map;
use function Tempest\root_path;
use function Tempest\Support\Path\to_relative_path;

final class DevelopmentException implements Response
{
    use IsResponse;

    public function __construct(Throwable $throwable, Response $response, Request $request)
    {
        $this->status = Status::INTERNAL_SERVER_ERROR;

        if (! Filesystem\exists(__DIR__ . '/local/dist/main.js')) {
            $this->body = 'The development exception interface is not built.';
            return;
        }

        $stacktrace = Stacktrace::fromThrowable($throwable, rootPath: root_path());

        if ($throwable instanceof ViewCompilationFailed) {
            $stacktrace = $this->enhanceStacktraceForViewCompilation($throwable, $stacktrace);
        }

        $this->body = new GenericView(
            path: __DIR__ . '/local/exception.view.php',
            data: [
                'script' => Filesystem\read_file(__DIR__ . '/local/dist/main.js'),
                'css' => Filesystem\read_file(__DIR__ . '/local/dist/style.css'),
                'hydration' => map([
                    'stacktrace' => $stacktrace,
                    'context' => $throwable instanceof ProvidesContext ? $throwable->context() : [],
                    'rootPath' => root_path(),
                    'request' => [
                        'uri' => $request->uri,
                        'method' => $request->method,
                        'headers' => $request->headers->toArray(),
                        'body' => $request->raw,
                    ],
                    'response' => [
                        'status' => $response->status->value,
                    ],
                    'resources' => [
                        'memoryPeakUsage' => memory_get_peak_usage(real_usage: true),
                        'executionTimeMs' => (hrtime(as_number: true) - TEMPEST_START) / 1_000_000,
                    ],
                    'versions' => [
                        'php' => PHP_VERSION,
                        'tempest' => Kernel::VERSION,
                    ],
                ])->toJson(),
            ],
        );
    }

    private function enhanceStacktraceForViewCompilation(ViewCompilationFailed $exception, Stacktrace $stacktrace): Stacktrace
    {
        $previous = $exception->getPrevious();

        if (! $previous) {
            return $stacktrace;
        }

        $lines = explode("\n", $exception->content);
        $errorLine = $previous->getLine();
        $contextLines = 5;
        $startLine = max(1, $errorLine - $contextLines);
        $endLine = min(count($lines), $errorLine + $contextLines);
        $snippetLines = [];

        for ($i = $startLine; $i <= $endLine; $i++) {
            $snippetLines[$i] = $lines[$i - 1];
        }

        return $stacktrace->prependFrame(new Frame(
            line: $errorLine,
            class: TempestViewRenderer::class,
            function: 'renderCompiled',
            type: '->',
            isVendor: false,
            snippet: new CodeSnippet(
                lines: $snippetLines,
                highlightedLine: $errorLine,
            ),
            absoluteFile: $exception->path,
            relativeFile: to_relative_path(root_path(), $exception->path),
            arguments: [],
            index: 1,
        ));
    }
}
