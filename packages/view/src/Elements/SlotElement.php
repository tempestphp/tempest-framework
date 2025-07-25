<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Parser\Token;
use Tempest\View\WithToken;

final class SlotElement implements Element, WithToken
{
    use IsElement;

    public function __construct(
        public readonly Token $token,
        public readonly string $name,
        array $attributes = [],
    ) {
        $this->attributes = $attributes;
    }

    public function matches(string $name): bool
    {
        return $this->name === $name;
    }

    public function compile(): string
    {
        $rendered = [];

        foreach ($this->getChildren() as $child) {
            $rendered[] = $child->compile();
        }

        return implode(PHP_EOL, $rendered);
    }
}
