<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\View;

final class TextElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly View $view,
        private readonly string $text,
    ) {}

    public function getText(): string
    {
        return $this->text;
    }
}