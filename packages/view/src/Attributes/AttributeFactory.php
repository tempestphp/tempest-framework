<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;

final readonly class AttributeFactory
{
    public function make(string $attributeName): Attribute
    {
        return match (true) {
            $attributeName === ':if' => new IfAttribute(),
            $attributeName === ':elseif' => new ElseIfAttribute(),
            $attributeName === ':else' => new ElseAttribute(),
            $attributeName === ':foreach' => new ForeachAttribute(),
            $attributeName === ':forelse' => new ForelseAttribute(),
            str_starts_with($attributeName, '::') => new EscapedExpressionAttribute($attributeName),
            str_starts_with($attributeName, ':') => new ExpressionAttribute($attributeName),
            default => new DataAttribute($attributeName),
        };
    }
}
