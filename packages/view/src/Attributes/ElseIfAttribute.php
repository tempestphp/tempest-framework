<?php

// draft

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpIfElement;
use Tempest\View\Exceptions\ElementWasInvalid;
use Tempest\View\ShouldBeRemoved;

final readonly class ElseIfAttribute implements Attribute, ShouldBeRemoved
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious()?->unwrap(PhpIfElement::class);

        if (! ($previous instanceof PhpIfElement)) {
            throw new ElementWasInvalid('There needs to be an if or elseif element before an elseif element.');
        }

        $previous->addElseif($element);

        return $element;
    }
}
