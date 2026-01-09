<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class WhitespaceElement implements Element
{
    use IsElement;

    public function __construct(
        public string $content,
    ) {}

    public function compile(): string
    {
        return $this->content;
    }
}
