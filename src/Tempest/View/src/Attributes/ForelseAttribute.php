<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpForeachElement;
use Tempest\View\Exceptions\InvalidElement;

final readonly class ForelseAttribute implements Attribute
{
    public function apply(Element $element): ?Element
    {
        $previous = $element->getPrevious()?->unwrap(PhpForeachElement::class);

        if (! $previous instanceof PhpForeachElement) {
            throw new InvalidElement('There needs to be a foreach element before an forelse element.');
        }

        $previous->setElse($element);

        return null;
    }
}
