<?php

declare(strict_types=1);

namespace Tempest\View\Components;

use Tempest\View\Elements\GenericElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class Submit implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-submit';
    }

    public function render(GenericElement $element, ViewRenderer $renderer): string
    {
        $label = $element->getAttribute('label') ?? 'Submit';

        return <<<HTML
<div>
<input type="submit" value="$label" />
</div>
HTML;
    }
}
