<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use function Tempest\Support\str;
use Tempest\View\Element;
use Tempest\View\Exceptions\InvalidElement;

final class PhpForeachElement implements Element
{
    use IsElement;

    private ?Element $else = null;

    public function __construct(
        private readonly Element $wrappingElement,
    ) {
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
        $compiled = sprintf(
            '<?php foreach (%s): ?>
%s',
            $this->wrappingElement->getAttribute('foreach'),
            $this->wrappingElement->compile(),
        );


        $compiled = sprintf(
            '%s
<?php endforeach; ?>',
            $compiled,
        );

        if ($this->else !== null) {
            $collectionName = str($this->wrappingElement->getAttribute('foreach'))
                ->match('/^(?<match>.*)\s+as/')['match'];

            $compiled = sprintf(
                '<?php if(count(%s)): ?>
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
