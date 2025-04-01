<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Exceptions\InvalidElement;
use Tempest\View\WrapsElement;

final class PhpIfElement implements Element, WrapsElement
{
    use IsElement;

    private ?Element $else = null;

    /** @var Element[] */
    private array $elseif = [];

    public function __construct(
        private readonly Element $wrappingElement,
    ) {}

    public function getWrappingElement(): Element
    {
        return $this->wrappingElement;
    }

    public function addElseif(Element $element): self
    {
        $this->elseif[] = $element;

        return $this;
    }

    public function setElse(Element $element): self
    {
        if ($this->else !== null) {
            throw new InvalidElement('There can only be one else element.');
        }

        $this->else = $element;

        return $this;
    }

    public function compile(): string
    {
        $compiled = sprintf(
            '<?php if(%s): ?>
                %s',
            $this->wrappingElement->consumeAttribute(':if'),
            $this->wrappingElement->compile(),
        );

        foreach ($this->elseif as $elseif) {
            $compiled = sprintf(
                '%s
                <?php elseif(%s): ?>
                %s',
                $compiled,
                $elseif->consumeAttribute(':elseif'),
                $elseif->compile(),
            );
        }

        if ($this->else !== null) {
            $this->else->consumeAttribute(':else');

            $compiled = sprintf(
                '%s
                <?php else: ?>
                %s',
                $compiled,
                $this->else->compile(),
            );
        }

        return sprintf(
            '%s
            <?php endif; ?>',
            $compiled,
        );
    }
}
