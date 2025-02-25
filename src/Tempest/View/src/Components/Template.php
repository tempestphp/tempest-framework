<?php

namespace Tempest\View\Components;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final class Template implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-template';
    }

    public function compile(ViewComponentElement $element): string
    {
        $content = [];

        foreach ($element->getChildren() as $child) {
            $content[] = $child->compile();
        }

        return implode('', $content);
    }
}