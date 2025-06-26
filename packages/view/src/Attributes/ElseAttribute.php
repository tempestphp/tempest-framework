<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\PhpIfElement;
use Tempest\View\Exceptions\ElementWasInvalid;

final readonly class ElseAttribute implements Attribute
{
    public function apply(Element $element): ?Element
    {
        $previous = $element->getPrevious()?->unwrap(PhpIfElement::class);

        if (! ($previous instanceof PhpIfElement)) {
            throw new ElementWasInvalid('There needs to be an if or elseif element before an else element.');
        }

        $previous->setElse($element);

        return null;
    }
}
