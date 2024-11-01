<?php

// draft

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpIfElement;
use Tempest\View\Exceptions\InvalidElement;

final readonly class ElseIfAttribute implements Attribute
{
    public function apply(Element $element): ?Element
    {
        $previous = $element->getPrevious();

        if (! $previous->is(PhpIfElement::class)) {
            throw new InvalidElement('There needs to be an if or elseif element before.');
        }

        $previous->addElseif($element);

        return null;
    }
}
