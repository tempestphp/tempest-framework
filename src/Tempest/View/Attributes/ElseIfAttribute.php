<?php

// draft

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;

final readonly class ElseIfAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        if (! $element instanceof GenericElement) {
            throw new Exception("Invalid element with :elseif");
        }

        $previous = $element->getPrevious();
        $previousCondition = false;

        if (! $previous instanceof GenericElement) {
            throw new Exception("Invalid preceding element before :elseif");
        }

        // Check all :elseif and :if conditions for previous elements
        // If one of the previous element's conditions is true, we'll stop.
        // We won't have to render this :elseif element
        while (
            $previousCondition === false
            && $previous instanceof GenericElement
            && ($previous->hasAttribute('if') || $previous->hasAttribute('elseif'))
        ) {
            $previousCondition = (bool) ($previous->getAttribute('if') ?? $previous->getAttribute('elseif'));
            $previous = $previous->getPrevious();
        }

        $currentCondition = (bool) $element->getAttribute('elseif');

        // For this element to render, the previous conditions need to be false,
        // and the current condition must be true
        if ($previousCondition === false && $currentCondition === true) {
            return $element;
        }

        return new EmptyElement();
    }
}
