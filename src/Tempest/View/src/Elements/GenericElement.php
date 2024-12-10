<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use function Tempest\Support\str;

final class GenericElement implements Element
{
    use IsElement;

    private array $rawAttributes = [];

    public function __construct(
        private readonly string $tag,
        array $attributes,
    ) {
        $this->attributes = $attributes;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function addRawAttribute(string $attribute): self
    {
        $this->rawAttributes[] = $attribute;

        return $this;
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

        $attributes = implode(' ', [...$attributes, ...$this->rawAttributes]);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }

        return "<{$this->tag}{$attributes}>{$content}</{$this->tag}>";
    }
}
