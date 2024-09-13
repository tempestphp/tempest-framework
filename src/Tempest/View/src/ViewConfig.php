<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\Reflection\ClassReflector;
use Tempest\View\Components\AnonymousViewComponent;
use Tempest\View\Exceptions\DuplicateViewComponent;
use Tempest\View\Renderers\TempestViewRenderer;

final class ViewConfig
{
    public function __construct(
        /** @var array<array-key, class-string<\Tempest\View\ViewComponent>|\Tempest\View\ViewComponent> */
        public array $viewComponents = [],

        /** @var class-string<\Tempest\View\ViewRenderer> */
        public string $rendererClass = TempestViewRenderer::class,
    ) {
    }

    public function addViewComponent(string $name, ClassReflector|AnonymousViewComponent $viewComponent): void
    {
        if (! str_starts_with($name, 'x-')) {
            $name = "x-{$name}";
        }

        if ($existing = $this->viewComponents[$name] ?? null) {
            throw new DuplicateViewComponent(
                name: $name,
                pending: $viewComponent,
                existing: $existing,
            );
        }

        if ($viewComponent instanceof ClassReflector) {
            $viewComponent = $viewComponent->getName();
        }

        $this->viewComponents[$name] = $viewComponent;
    }
}
