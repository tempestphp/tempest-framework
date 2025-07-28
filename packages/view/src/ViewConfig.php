<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Reflection\ClassReflector;
use Tempest\View\Components\ViewComponent;
use Tempest\View\Exceptions\ViewComponentWasAlreadyRegistered;
use Tempest\View\Renderers\TempestViewRenderer;

final class ViewConfig
{
    public function __construct(
        /** @var array<array-key, ViewComponent> */
        public array $viewComponents = [],

        /** @var class-string<\Tempest\View\ViewProcessor>[] */
        public array $viewProcessors = [],

        /** @var class-string<\Tempest\View\ViewRenderer> */
        public string $rendererClass = TempestViewRenderer::class,
    ) {}

    public function addViewProcessor(ClassReflector $viewProcessor): void
    {
        $this->viewProcessors[] = $viewProcessor->getName();
    }

    public function addViewComponent(ViewComponent $viewComponent): void
    {
        $existing = $this->viewComponents[$viewComponent->name] ?? null;

        if ($existing?->isVendorComponent === false) {
            throw new ViewComponentWasAlreadyRegistered(
                pending: $viewComponent,
                existing: $existing,
            );
        }

        $this->viewComponents[$viewComponent->name] = $viewComponent;
    }
}
