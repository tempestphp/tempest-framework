<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\View;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\View\GenericView;
use Tempest\View\Renderers\TempestViewRenderer;
use Tempest\View\ViewConfig;

final class ViewRenderBench
{
    private TempestViewRenderer $renderer;

    private TempestViewRenderer $rendererWithComponents;

    private string $fixturesPath;

    public function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/Fixtures';

        $this->renderer = TempestViewRenderer::make();

        $this->rendererWithComponents = TempestViewRenderer::make(
            viewConfig: new ViewConfig()->addViewComponents(
                $this->fixturesPath . '/x-bench-layout.view.php',
            ),
        );
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchPlainHtml(): void
    {
        $this->renderer->render(
            new GenericView($this->fixturesPath . '/plain.view.php'),
        );
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchExpressions(): void
    {
        $view = new GenericView($this->fixturesPath . '/expressions.view.php');
        $view->data(
            title: 'Benchmark',
            heading: 'Hello World',
            name: 'Tempest',
            count: '42',
            footer: 'Copyright 2025',
        );

        $this->renderer->render($view);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchControlFlow(): void
    {
        $view = new GenericView($this->fixturesPath . '/control-flow.view.php');
        $view->data(
            title: 'Benchmark',
            heading: 'Hello World',
            showHeader: true,
            isAdmin: false,
            items: ['Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item 5'],
        );

        $this->renderer->render($view);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchViewComponent(): void
    {
        $view = new GenericView($this->fixturesPath . '/component.view.php');
        $view->data(
            title: 'Benchmark',
            heading: 'Hello World',
            items: ['Item 1', 'Item 2', 'Item 3', 'Item 4', 'Item 5'],
        );

        $this->rendererWithComponents->render($view);
    }
}
