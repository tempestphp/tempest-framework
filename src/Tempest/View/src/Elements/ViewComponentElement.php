<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\Core\Environment;
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
        private readonly Environment $environment,
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
            if (! ($child instanceof SlotElement)) {
                continue;
            }

            $slots[] = $child;
        }

        return $slots;
    }

    public function getSlot(string $name = 'slot'): ?Element
    {
        foreach ($this->getChildren() as $child) {
            if (! ($child instanceof SlotElement)) {
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
            ->prepend(
                // Add attributes to the current scope
                '<?php $_previousAttributes = $attributes ?? null; ?>',
                sprintf('<?php $attributes = %s; ?>', var_export($this->attributes, true)),  // @mago-expect best-practices/no-debug-symbols Set the new value of $attributes for this view component

                // Add dynamic slots to the current scope
                '<?php $_previousSlots = $slots ?? null; ?>', // Store previous slots in temporary variable to keep scope
                sprintf('<?php $slots = %s; ?>', var_export($slots, true)), // @mago-expect best-practices/no-debug-symbols Set the new value of $slots for this view component
            )

            ->append(
                // Restore previous slots
                '<?php unset($slots); ?>',
                '<?php $slots = $_previousSlots ?? null; ?>',
                '<?php unset($_previousSlots); ?>',

                // Restore previous attributes
                '<?php unset($attributes); ?>',
                '<?php $attributes = $_previousAttributes ?? null; ?>',
                '<?php unset($_previousAttributes); ?>',
            )
            // Compile slots
            ->replaceRegex(
                regex: '/<x-slot\s*(name="(?<name>\w+)")?((\s*\/>)|><\/x-slot>)/',
                replace: function ($matches) {
                    $name = $matches['name'] ?: 'slot';

                    $slot = $this->getSlot($name);

                    if ($slot === null) {
                        // A slot doesn't have any content, so we'll comment it out.
                        // This is to prevent DOM parsing errors (slots in <head> tags is one example, see #937)
                        return $this->environment->isProduction() ? '' : ('<!--' . $matches[0] . '-->');
                    }

                    return $slot->compile();
                },
            );

        return $this->compiler->compile($compiled->toString());
    }
}
