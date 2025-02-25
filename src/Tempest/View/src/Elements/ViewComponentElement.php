<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;
use Tempest\View\Slot;
use Tempest\View\ViewComponent;
use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ViewComponentElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly TempestViewCompiler $compiler,
        private readonly ViewComponent $viewComponent,
        array $attributes,
    ) {
        $this->attributes = $attributes;
    }

    public function getViewComponent(): ViewComponent
    {
        return $this->viewComponent;
    }

    /** @return Element[] */
    public function getSlots(): array
    {
        $slots = [];

        foreach ($this->getChildren() as $child) {
            if (! $child instanceof SlotElement) {
                continue;
            }

            $slots[] = $child;
        }

        return $slots;
    }

    public function getSlot(string $name = 'slot'): ?Element
    {
        foreach ($this->getChildren() as $child) {
            if (! $child instanceof SlotElement) {
                continue;
            }

            if ($child->matches($name)) {
                return $child;
            }
        }

        if ($name === 'slot') {
            $elements = [];

            foreach ($this->getChildren() as $child) {
                if ($child instanceof SlotElement) {
                    continue;
                }

                $elements[] = $child;
            }

            return new CollectionElement($elements);
        }

        return null;
    }

    public function compile(): string
    {
        /** @var Slot[] $slots */
        $slots = arr($this->getSlots())
            ->mapWithKeys(fn (SlotElement $element) => yield $element->name => Slot::fromElement($element))
            ->toArray();

        $compiled = str($this->viewComponent->compile($this))
            // Add slots list
            ->prepend(sprintf('<?php $slots = %s; ?>', var_export($slots, true)))

            // Compile slots
            ->replaceRegex(
                regex: '/<x-slot\s*(name="(?<name>\w+)")?((\s*\/>)|><\/x-slot>)/',
                replace: function ($matches) {
                    $name = $matches['name'] ?: 'slot';

                    $slot = $this->getSlot($name);

                    if ($slot === null) {
                        return $matches[0];
                    }

                    return $slot->compile();
                },
            );

        return $this->compiler->compile($compiled->toString());
    }
}
