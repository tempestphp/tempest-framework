<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use function Tempest\Support\str;
use Tempest\View\Element;

final class GenericElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $tag,
        array $attributes,
    ) {
        $this->attributes = $attributes;
    }

    public function compile(): string
    {
        $content = [];

        foreach ($this->getChildren() as $child) {
            $content[] = $child->compile();
        }

        $content = implode('', $content);

        $attributes = [];

        foreach ($this->getAttributes() as $name => $value) {
            $name = str($name);

            if ($name->startsWith(':')) {
                $name = ':' . $name->kebab()->toString();
            } else {
                $name = $name->kebab()->toString();
            }

            if ($value) {
                $attributes[] = $name . '="' . $value . '"';
            } else {
                $attributes[] = $name;
            }
        }

        $attributes = implode(' ', $attributes);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }

        return "<{$this->tag}{$attributes}>{$content}</{$this->tag}>";
    }
}
