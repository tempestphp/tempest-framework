<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewComponent;

final class ViewComponentElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly ViewComponent $viewComponent,
        array $attributes,
    ) {
        $this->attributes = $attributes;
    }

    public function getViewComponent(): ViewComponent
    {
        return $this->viewComponent;
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
        return preg_replace_callback(
            pattern: '/<x-slot\s*(name="(?<name>\w+)")?((\s*\/>)|><\/x-slot>)/',
            callback: function ($matches) {
                $name = $matches['name'] ?: 'slot';

                $slot = $this->getSlot($name);

                if ($slot === null) {
                    return $matches[0];
                }

                return $slot->compile();
            },
            subject: $this->viewComponent->compile($this),
        );
    }
}
