<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final class TextElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly View $view,
        private readonly string $text,
        private readonly ?Element $previous,
        private readonly array $attributes,
        private array $data = [],
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        return preg_replace_callback(
            // TODO: make this-> optional
            pattern: '/{{\s*(?<eval>\$this->.*?)\s*}}/',
            callback: function (array $matches) : string {
                $viewClone = clone $this->view;

                $viewClone->data(...$this->getData());

                return $viewClone->eval($matches['eval']) ?? '';
            },
            subject: $this->text,
        );
    }
}