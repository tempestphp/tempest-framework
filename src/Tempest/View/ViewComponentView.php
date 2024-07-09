<?php

declare(strict_types=1);

namespace Tempest\View;

final class ViewComponentView implements View
{
    use IsView;

    public function __construct(
        private readonly View $wrappingView,
        string $content,
    )
    {
        $this->path = $content;
    }

    public function __get(string $name): mixed
    {
        return $this->wrappingView->get($name);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->wrappingView->{$name}(...$arguments);
    }
}
