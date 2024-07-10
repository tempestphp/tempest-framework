<?php

declare(strict_types=1);

namespace Tempest\View;

use ReflectionClass;
use Tempest\View\Components\AnonymousViewComponent;
use Tempest\View\Exceptions\DuplicateViewComponent;

final class ViewConfig
{
    public function __construct(
        /** @var array<array-key, class-string<\Tempest\View\ViewComponent>|\Tempest\View\ViewComponent> */
        public array $viewComponents = [],
    ) {
    }

    public function addViewComponent(string $name, ReflectionClass|AnonymousViewComponent $viewComponent): void
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

        if ($viewComponent instanceof ReflectionClass) {
            $viewComponent = $viewComponent->getName();
        }

        $this->viewComponents[$name] = $viewComponent;
    }
}
