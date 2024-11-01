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
        $previous = $element->getPrevious();

        if (! $previous?->is(PhpForeachElement::class)) {
            throw new InvalidElement('No valid foreach loop found in preceding element');
        }

        /** @var PhpForeachElement $previous */
        $previous->setElse($element);

        return null;
    }
}
