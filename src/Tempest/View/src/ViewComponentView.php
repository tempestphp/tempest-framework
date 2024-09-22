<?php

declare(strict_types=1);

namespace Tempest\View;

final class ViewComponentView implements View
{
    use IsView;

    public function __construct(
        private readonly View $wrappingView,
        private readonly Element $wrappingElement,
        string $content,
    ) {
        $this->path = $content;
    }

    public function getData(): array
    {
        return $this->wrappingElement->getData();
    }

    public function __get(string $name): mixed
    {
        return $this->wrappingElement->getData($name);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->wrappingView->{$name}(...$arguments);
    }
}
