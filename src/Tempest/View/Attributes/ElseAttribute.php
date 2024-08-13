<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;

final readonly class ElseAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();
        $previousCondition = false;

        if (! $previous instanceof GenericElement) {
            throw new Exception("Invalid preceding element before :else");
        }

        // Check all :elseif and :if conditions for previous elements
        // If one of the previous element's conditions is true, we'll stop.
        // We won't have to render this :else element
        while (
            $previousCondition === false
            && $previous instanceof GenericElement
            && ($previous->hasAttribute('if') || $previous->hasAttribute('elseif'))
        ) {
            $previousCondition = (bool) ($previous->getAttribute('if') ?? $previous->getAttribute('elseif'));
            $previous = $previous->getPrevious();
        }

        if ($previousCondition) {
            return new EmptyElement();
        }

        return $element;
    }
}
