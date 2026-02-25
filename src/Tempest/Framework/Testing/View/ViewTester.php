<?php

declare(strict_types=1);

namespace Tempest\Framework\Testing\View;

use Tempest\Container\Container;
use Tempest\View\GenericView;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewConfig;
use Tempest\View\ViewRenderer;

/**
 * Provides utilities for testing views.
 */
final class ViewTester
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * Renders a view template and returns the output as a string.
     */
    public function render(string|View $view, mixed ...$params): string
    {
        if (is_string($view)) {
            $view = new GenericView($view);
        }

        $view->data(...$params);

        return $this->container->get(ViewRenderer::class)->render($view);
    }

    /**
     * Registers a view component for testing purposes.
     */
    public function registerViewComponent(string $name, string $html, ?string $file = null, bool $isVendor = false): self
    {
        $viewComponent = new ViewComponent(
            name: $name,
            contents: $html,
            file: $file ?? $name . '.view.php',
            isVendorComponent: $isVendor,
        );

        $this->container->get(ViewConfig::class)->addViewComponent($viewComponent);

        return $this;
    }
}
