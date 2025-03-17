<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Stringable;
use Tempest\Container\Container;
use Tempest\Support\Html\HtmlString;
use Tempest\View\Exceptions\ViewCompilationError;
use Tempest\View\Exceptions\ViewVariableIsReserved;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewCache;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;
use Throwable;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final class TempestViewRenderer implements ViewRenderer
{
    private ?View $currentView = null;

    public function __construct(
        private readonly TempestViewCompiler $compiler,
        private readonly ViewCache $viewCache,
        private readonly ViewConfig $viewConfig,
        private readonly Container $container,
    ) {
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

        $path = $this->viewCache->getCachedViewPath(
            path: $view->path,
            compiledView: fn () => $this->cleanupCompiled($this->compiler->compile($view)),
        );

        $view = $this->processView($view);

        return $this->renderCompiled($view, $path);
    }

    private function cleanupCompiled(string $compiled): string
    {
        // Remove strict type declarations
        $compiled = str($compiled)->replace('declare(strict_types=1);', '');

        // Cleanup and bundle imports
        $imports = arr();

        $compiled = $compiled->replaceRegex("/^\s*use (function )?.*;/m", function (array $matches) use (&$imports) {
            $imports[$matches[0]] = $matches[0];

            return '';
        });

        $compiled = $compiled->prepend(
            sprintf(
                '<?php
%s
?>',
                $imports->implode(PHP_EOL),
            ),
        );

        // Remove empty PHP blocks
        $compiled = $compiled->replaceRegex('/<\?php\s*\?>/', '');

        return $compiled->toString();
    }

    private function processView(View $view): View
    {
        foreach ($this->viewConfig->viewProcessors as $viewProcessorClass) {
            /** @var \Tempest\View\ViewProcessor $viewProcessor */
            $viewProcessor = $this->container->get($viewProcessorClass);

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

        try {
            include $_path;
        } catch (Throwable $throwable) {
            throw new ViewCompilationError(
                path: $_path,
                content: file_get_contents($_path),
                previous: $throwable,
            );
        }

        $this->currentView = null;

        return trim(ob_get_clean());
    }

    public function escape(null|string|HtmlString|Stringable $value): string
    {
        if ($value instanceof HtmlString) {
            return (string) $value;
        }

        return htmlentities((string) $value);
    }

    private function validateView(View $view): void
    {
        $data = $view->data;

        if (array_key_exists('slots', $data)) {
            throw new ViewVariableIsReserved('slots');
        }
    }
}
