<?php


declare(strict_types=1);

namespace Tempest\View\Elements;

use function Tempest\Support\str;
use Tempest\View\Element;
use Tempest\View\Renderers\TempestViewCompiler;
use Tempest\View\ViewComponent;

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
        $compiled = str($this->viewComponent->compile($this))
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
