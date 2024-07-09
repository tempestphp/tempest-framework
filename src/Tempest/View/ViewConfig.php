<?php

declare(strict_types=1);

namespace Tempest\View;

use ReflectionClass;
use Tempest\View\Components\AnonymousViewComponent;

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

        // check for duplicates

        if ($viewComponent instanceof ReflectionClass) {
            $viewComponent = $viewComponent->getName();
        }

        $this->viewComponents[$name] = $viewComponent;
    }
}
