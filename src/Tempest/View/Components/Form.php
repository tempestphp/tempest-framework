<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Element;
use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class Form /*implements ViewComponent*/
{
    public function __construct(
        private string $action,
        private string $method = 'post',
        private ?View $slot = null,
    ) {
    }

    public static function getName(): string
    {
        return 'x-form';
    }

    public function render(ViewRenderer $renderer, Element $element): string
    {
        $slot = $renderer->render($this->slot);

        return <<<HTML
<form action="{$this->action}" method="{$this->method}">
    {$slot}
</form>
HTML;
    }
}
