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
        if(!$element instanceof GenericElement) {
            return $element;
        }

        $previous = $element->getPrevious();


        $condition = $element->getAttribute('elseif');

        if ($condition) {
            return $element;
        } else {
            return new EmptyElement;
        }
    }
}
