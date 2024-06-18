<?php

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\Elements\GenericElement;

final readonly class ForelseAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();
            
        if (
            ! $previous instanceof GenericElement
            || ! $previous->hasAttribute('foreach')
        ) {
            throw new Exception('No valid foreach loop found in preceding element');
        }
        
        $foreach = $previous->getAttribute('foreach', eval: false);

        preg_match(
            '/\$this->(?<collection>\w+) as \$(?<item>\w+)/',
            $foreach,
            $matches,
        );

        $collection = $element->getData()[$matches['collection']] ?? [];

        if ($collection) {
            return new EmptyElement($element);
        } else {
            return $element;
        }
    }
}