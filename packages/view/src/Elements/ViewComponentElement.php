<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\Core\Environment;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;
use Tempest\View\Element;
use Tempest\View\Parser\TempestViewCompiler;
use Tempest\View\Parser\TempestViewParser;
use Tempest\View\Slot;
use Tempest\View\ViewComponent;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ViewComponentElement implements Element
{
    use IsElement;

    private array $dataAttributes;

    public function __construct(
        private readonly Environment $environment,
        private readonly TempestViewCompiler $compiler,
        private readonly ViewComponent $viewComponent,
        array $attributes,
    ) {
        $this->attributes = $attributes;
        $this->dataAttributes = arr($attributes)
            ->filter(fn ($_, $key) => ! str_starts_with($key, ':'))
            // Attributes are converted to camelCase by default for PHP variable usage, but in the context of data attributes, kebab case is good
            ->mapWithKeys(fn ($value, $key) => yield str($key)->kebab()->toString() => $value)
            ->toArray();
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

        $compiled = str($this->viewComponent->compile($this));

        $compiled = $compiled
            // Fallthrough attributes
            ->replaceRegex(
                regex: '/^<(?<tag>[\w-]+)(.*?["\s])?>/', // Match the very first opening tag, this will never fail.
                replace: function ($matches) {
                    /** @var \Tempest\View\Parser\Token $token */
                    $token = TempestViewParser::ast($matches[0])[0];

                    $attributes = arr($token->htmlAttributes)->map(fn (string $value) => new MutableString($value));

                    foreach (['class', 'style', 'id'] as $attributeName) {
                        if (! isset($this->dataAttributes[$attributeName])) {
                            continue;
                        }

                        $attributes[$attributeName] ??= new MutableString();

                        if ($attributeName === 'id') {
                            $attributes[$attributeName] = new MutableString(' ' . $this->dataAttributes[$attributeName]);
                        } else {
                            $attributes[$attributeName]->append(' ' . $this->dataAttributes[$attributeName]);
                        }
                    }

                    return sprintf(
                        '<%s%s>',
                        $matches['tag'],
                        $attributes
                            ->map(function (MutableString $value, string $key) {
                                return sprintf('%s="%s"', $key, $value->trim());
                            })
                            ->implode(' ')
                            ->when(
                                fn (ImmutableString $string) => $string->isNotEmpty(),
                                fn (ImmutableString $string) => $string->prepend(' '),
                            ),
                    );
                },
            );

        $compiled = $compiled
            ->prepend(
                // Add attributes to the current scope
                '<?php $_previousAttributes = $attributes ?? null; ?>',
                sprintf('<?php $attributes = \Tempest\Support\arr(%s); ?>', var_export($this->dataAttributes, true)), // @mago-expect best-practices/no-debug-symbols Set the new value of $attributes for this view component

                // Add dynamic slots to the current scope
                '<?php $_previousSlots = $slots ?? null; ?>', // Store previous slots in temporary variable to keep scope
                sprintf('<?php $slots = \Tempest\Support\arr(%s); ?>', var_export($slots, true)), // @mago-expect best-practices/no-debug-symbols Set the new value of $slots for this view component
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
                regex: '/<x-slot\s*(name="(?<name>[\w-]+)")?((\s*\/>)|>(?<default>(.|\n)*?)<\/x-slot>)/',
                replace: function ($matches) {
                    $name = $matches['name'] ?: 'slot';

                    $slot = $this->getSlot($name);

                    $default = $matches['default'];

                    if ($slot === null) {
                        if ($default) {
                            return $default;
                        }

                        // A slot doesn't have any content, so we'll comment it out.
                        // This is to prevent DOM parsing errors (slots in <head> tags is one example, see #937)
                        return $this->environment->isProduction() ? '' : ('<!--' . $matches[0] . '-->');
                    }

                    $compiled = $slot->compile();

                    if (trim($compiled) === '') {
                        return $default;
                    }

                    return $compiled;
                },
            );

        return $this->compiler->compile($compiled->toString());
    }
}
