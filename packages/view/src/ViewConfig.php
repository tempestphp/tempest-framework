<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Reflection\ClassReflector;
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

    public function addViewComponent(ViewComponent $pending): void
    {
        $existing = $this->viewComponents[$pending->name] ?? null;

        if ($existing && $pending->isVendorComponent) {
            // Vendor components don't overwrite existing components
            return;
        }

        if ($existing?->isProjectComponent && $pending->isProjectComponent) {
            // If both pending and existing are project components, we'll throw an exception
            throw new ViewComponentWasAlreadyRegistered(
                pending: $pending,
                existing: $existing,
            );
        }

        $this->viewComponents[$pending->name] = $pending;
    }
}
