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

    public function addViewComponent(string $name, ClassReflector|ViewComponent $viewComponent): void
    {
        if (! str_starts_with($name, 'x-')) {
            $name = "x-{$name}";
        }

        if ($existing = $this->viewComponents[$name] ?? null) {
            if (($existing->isVendorComponent ?? null) === false) {
                throw new ViewComponentWasAlreadyRegistered(
                    name: $name,
                    pending: $viewComponent,
                    existing: $existing,
                );
            }
        }

        if ($viewComponent instanceof ClassReflector) {
            $viewComponent = $viewComponent->getName();
        }

        $this->viewComponents[$name] = $viewComponent;
    }
}
