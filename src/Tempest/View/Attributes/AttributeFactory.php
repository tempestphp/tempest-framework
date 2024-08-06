<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\View;

final readonly class AttributeFactory
{
    public function make(View $view, string $name, ?string $value): Attribute
    {
        return match(true) {
            $name === ':if' => new IfAttribute(),
            $name === ':elseif' => new ElseIfAttribute(),
            $name === ':else' => new ElseAttribute(),
            $name === ':foreach' => new ForeachAttribute($view, $value),
            $name === ':forelse' => new ForelseAttribute(),
            str_starts_with(':', $name) && $value => new DataAttribute($view, $name, $value),
            default => new DefaultAttribute(),
        };
    }
}
