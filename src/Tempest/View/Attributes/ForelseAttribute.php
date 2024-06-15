<?php

namespace Tempest\View\Attributes;

use Exception;
use Tempest\View\Attribute;
use Tempest\View\Element;
use Tempest\View\Elements\EmptyElement;
use Tempest\View\View;

final readonly class ForelseAttribute implements Attribute
{
    public function __construct(
        private View $view,
    ) {}

    public function apply(Element $element): Element
    {
        $foreach = $element->getPrevious()?->getAttribute(':foreach');

        if (! $foreach) {
            throw new Exception('No valid foreach found in previous element');
        }

        preg_match(
            '/\$this->(?<collection>\w+) as \$(?<item>\w+)/',
            $foreach,
            $matches,
        );

        $collection = $this->view->get($matches['collection']);

        if ($collection) {
            return new EmptyElement($element);
        } else {
            return $element;
        }
    }
}