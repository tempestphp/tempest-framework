<?php

declare(strict_types=1);

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
        return match(true) {
            $name === ':if' => new IfAttribute(),
            $name === ':else' => new ElseAttribute(),
            $name === ':foreach' => new ForeachAttribute($view, $value),
            $name === ':forelse' => new ForelseAttribute(),
            str_starts_with(':', $name) && $value => new DataAttribute($view, $name, $value),
            default => new DefaultAttribute($value),
        };
    }
}
