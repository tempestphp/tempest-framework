<?php

declare(strict_types=1);

namespace Tempest\View;

use ReflectionClass;

final class ViewConfig
{
    public function __construct(
        /** @var array<array-key, class-string<\Tempest\View\ViewComponent>|\Tempest\View\ViewComponent> */
        public array $viewComponents = [],
    ) {
    }

    /**
     * @param ReflectionClass $viewComponentClass
     * @return void
     */
    public function addViewComponent(ReflectionClass $viewComponentClass): void
    {
        $name = forward_static_call($viewComponentClass->getName() . '::getName');

        if (! str_starts_with($name, 'x-')) {
            $name = "x-{$name}";
        }

        $this->viewComponents[$name] = $viewComponentClass->getName();
    }
}
