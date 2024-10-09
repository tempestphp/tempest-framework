<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;

final readonly class AttributeFactory
{
    public function make(string $name): Attribute
    {
        return match(true) {
            $name === ':if' => new IfAttribute(),
            $name === ':elseif' => new ElseIfAttribute(),
            $name === ':else' => new ElseAttribute(),
            $name === ':foreach' => new ForeachAttribute(),
            $name === ':forelse' => new ForelseAttribute(),
            str_starts_with($name, ':') => new DataAttribute($name),
            default => new DefaultAttribute(),
        };
    }
}
