<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Parser\Token;
use Tempest\View\WithToken;

use function Tempest\Support\str;

final class RawElement implements Element, WithToken
{
    use IsElement;

    public function __construct(
        public readonly Token $token,
        private readonly ?string $tag,
        private readonly string $content,
        array $attributes = [],
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

        $attributes = implode(' ', [...$attributes, ...$this->rawAttributes]);

        if ($attributes !== '') {
            $attributes = ' ' . $attributes;
        }

        return "<{$this->tag}{$attributes}>{$this->content}</{$this->tag}>";
    }
}
