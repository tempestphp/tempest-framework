<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use function Tempest\Support\str;

final class RawElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly ?string $tag,
        private readonly string $content,
        array $attributes,
    ) {
        $this->attributes = $attributes;
    }

    public function compile(): string
    {
        if ($this->tag === null) {
            return $this->content;
        }

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

        return "<{$this->tag}{$attributes}>{$this->content}</{$this->tag}>";
    }
}
