<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;

final class TextElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly string $text,
    ) {
    }

    public function getText(): string
    {
        return $this->text;
    }
}
