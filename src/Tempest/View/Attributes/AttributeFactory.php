<?php

namespace Tempest\View\Attributes;

use PHPHtmlParser\Dom\AbstractNode;
use Tempest\View\Attribute;
use Tempest\View\View;

final readonly class AttributeFactory
{
    /** @return Attribute[] */
    public function makeCollection(View $view, AbstractNode $node): array
    {
        $attributes = [];

        foreach ($node->getAttributes() as $name => $value) {
            $attributes[] = $this->make($view, $name, $value);
        }

        return $attributes;
    }

    public function make(View $view, string $name, ?string $value): Attribute
    {
        return match($name) {
            ':if' => new IfAttribute($view, $value),
            ':else' => new ElseAttribute($view),
            ':foreach' => new ForeachAttribute($view, $value),
            ':forelse' => new ForelseAttribute($view),
            default => new DefaultAttribute($value),
        };
    }
}