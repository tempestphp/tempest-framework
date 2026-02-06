<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

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

        $elementFactory = new ElementFactory(
            $viewConfig,
            $environment,
        );

        $compiler = new TempestViewCompiler(
            elementFactory: $elementFactory,
            attributeFactory: new AttributeFactory(),
        );

        $elementFactory->setViewCompiler($compiler);

        $viewCache ??= ViewCache::create(enabled: false);

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

        $path = $this->viewCache->getCachedViewPath(
            path: $view->path,
            compiledView: fn () => $this->compiler->compile($view),
        );

        $view = $this->processView($view);

        return $this->renderCompiled($view, $path);
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

        try {
            include $_path;
        } catch (Throwable $throwable) {
            ob_end_clean(); // clean buffer before rendering exception

            throw new ViewCompilationFailed(
                path: $_path,
                content: Filesystem\read_file($_path),
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
}
