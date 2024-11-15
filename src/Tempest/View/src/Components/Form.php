<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class Form implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-form';
    }

    public function compile(ViewComponentElement $element): string
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
