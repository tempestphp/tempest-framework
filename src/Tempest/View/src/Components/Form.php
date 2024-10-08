<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class Form implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-form';
    }

    public function render(ViewComponentElement $element, ViewRenderer $renderer): string
    {
        $action = $element->getAttribute('action');
        $method = $element->getAttribute('method') ?? 'post';
        $enctype = $element->hasAttribute('enctype') ? sprintf('enctype="%s"', $element->getAttribute('enctype')) : '';

        return <<<HTML
<form action="{$action}" method="{$method}" {$enctype}>
    <x-slot />
</form>
HTML;
    }
}
