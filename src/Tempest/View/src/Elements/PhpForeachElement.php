<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use function Tempest\Support\str;
use Tempest\View\Element;
use Tempest\View\Exceptions\InvalidElement;
use Tempest\View\WrapsElement;

final class PhpForeachElement implements Element, WrapsElement
{
    use IsElement;

    private ?Element $else = null;

    public function __construct(
        private readonly Element $wrappingElement,
    ) {
    }

    public function getWrappingElement(): Element
    {
        return $this->wrappingElement;
    }

    public function setElse(Element $element): self
    {
        if ($this->else !== null) {
            throw new InvalidElement('There can only be one forelse element.');
        }

        $this->else = $element;

        return $this;
    }

    public function compile(): string
    {
        $foreachAttribute = $this->wrappingElement->consumeAttribute('foreach');

        $compiled = sprintf(
            '<?php foreach (%s): ?>
%s',
            $foreachAttribute,
            $this->wrappingElement->compile(),
        );


        $compiled = sprintf(
            '%s
<?php endforeach; ?>',
            $compiled,
        );

        if ($this->else !== null) {
            $collectionName = str($foreachAttribute)->match('/^(?<match>.*)\s+as/')['match'];

            $this->else->consumeAttribute('forelse');

            $compiled = sprintf(
                '<?php if(iterator_count(%s ?? [])): ?>
%s
<?php else: ?>
%s
<?php endif ?>',
                $collectionName,
                $compiled,
                $this->else->compile(),
            );
        }

        return $compiled;
    }
}
