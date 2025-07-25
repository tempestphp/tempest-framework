<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Parser\Token;
use Tempest\View\WithToken;

final class TemplateElement implements Element, WithToken
{
    use IsElement;

    public function __construct(
        public readonly Token $token,
        array $attributes = [],
    ) {
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
