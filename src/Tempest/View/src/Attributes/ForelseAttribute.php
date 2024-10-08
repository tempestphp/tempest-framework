<?php

declare(strict_types=1);

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;

final readonly class ForelseAttribute implements Attribute
{
    public function apply(Element $element): Element
    {
        $previous = $element->getPrevious();

        if (! $previous?->hasAttribute('foreach')) {
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
            return new EmptyElement();
        }

        return $element;
    }
}
