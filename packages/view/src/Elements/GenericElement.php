<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Parser\Token;
use Tempest\View\WithToken;

use function Tempest\Support\Html\is_void_tag;

final class GenericElement implements Element, WithToken
{
    use IsElement;

    private ?string $tagExpression = null;

    public function __construct(
        public readonly Token $token,
        private string $tag,
        private readonly bool $isHtml,
        array $attributes,
    ) {
        $this->attributes = $attributes;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function withTag(string $tag): self
    {
        $clone = clone $this;
        $clone->tag = $tag;

        return $clone;
    }

    public function withTagExpression(string $expression): self
    {
        $clone = clone $this;
        $clone->tagExpression = $expression;

        return $clone;
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
            if ($value !== null && $value !== '') {
                $attributes[] = $name . '="' . $value . '"';
            } else {
                $attributes[] = $name;
            }
        }

        $attributes = implode(' ', [...$attributes, ...$this->rawAttributes]);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }

        // When a PHP expression drives the tag, we cannot know the resolved
        // name at compile time, so void-element detection is skipped and we
        // always emit a full open/close pair.
        if ($this->tagExpression !== null) {
            $open = "<?= {$this->tagExpression} ?>";
            $close = "<?= {$this->tagExpression} ?>";

            return "<{$open}{$attributes}>{$content}</{$close}>";
        }

        // Void elements
        if (is_void_tag($this->tag)) {
            if ($this->isHtml) {
                return "<{$this->tag}{$attributes}>";
            } else {
                return "<{$this->tag}{$attributes} />";
            }
        }

        return "<{$this->tag}{$attributes}>{$content}</{$this->tag}>";
    }
}
