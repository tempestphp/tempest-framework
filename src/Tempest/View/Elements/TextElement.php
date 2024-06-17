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
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        return preg_replace_callback(
            pattern: '/{{\s*(?<eval>\$.*?)\s*}}/',
            callback: function (array $matches): string {
                $eval = $matches['eval'] ?? '';

                if (str_starts_with($eval, '$this->')) {
                    return $this->view->eval($eval) ?? '';
                }

                return $this->getData()[ltrim($eval, '$')];
            },
            subject: $this->text,
        );
    }
}