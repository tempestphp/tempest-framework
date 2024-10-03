<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\GenericElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class Form implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-form';
    }

    public function isMultipart(GenericElement $element): bool
    {
        return $element->hasAttribute('enctype');
    }

    public function render(GenericElement $element, ViewRenderer $renderer): string
    {
        $action = $element->getAttribute('action');
        $method = $element->getAttribute('method') ?? 'post';
        $enctype = $this->isMultipart($element) 
            ? "enctype=\"{$element->getAttribute('enctype')}\""
            : '';

        return <<<HTML
<form action="{$action}" method="{$method}" {$enctype}>
    <x-slot />
</form>
HTML;
    }
}
