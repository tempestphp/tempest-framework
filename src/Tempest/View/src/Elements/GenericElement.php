<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

use function Tempest\Support\Html\is_void_tag;
use function Tempest\Support\str;

final class GenericElement implements Element
{
    use IsElement;

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

    public function compile(): string
    {
        $content = [];

        foreach ($this->getChildren() as $child) {
            $content[] = $child->compile();
        }

        $content = implode('', $content);

        $attributes = [];

        foreach ($this->getAttributes() as $name => $value) {
            if ($value) {
                $attributes[] = $name . '="' . $value . '"';
            } else {
                $attributes[] = $name;
            }
        }

        $attributes = implode(' ', [...$attributes, ...$this->rawAttributes]);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
            $attributes = str_replace(
                ['?> <?php', '?> <?='],
                ['?><?php', '?><?='],
                $attributes,
            );
        }

        // Void elements
        if (is_void_tag($this->tag)) {
            return "<{$this->tag}{$attributes}>";
        }

        return "<{$this->tag}{$attributes}>{$content}</{$this->tag}>";
    }
}
