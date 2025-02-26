<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class TemplateElement implements Element
{
    use IsElement;

    public function __construct(
        array $attributes = [],
    )
    {
        $this->attributes = $attributes;
    }

    public function compile(): string
    {
        $content = [];

        foreach ($this->getChildren() as $child) {
            $content[] = $child->compile();
        }

        return implode('', $content);
    }
}