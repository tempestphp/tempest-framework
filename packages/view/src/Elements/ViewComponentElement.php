<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\Core\Environment;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;
use Tempest\View\Element;
use Tempest\View\Export\ViewObjectExporter;
use Tempest\View\Parser\TempestViewCompiler;
use Tempest\View\Parser\TempestViewParser;
use Tempest\View\Parser\Token;
use Tempest\View\Slot;
use Tempest\View\ViewComponent;
use Tempest\View\WithToken;

use function Tempest\Support\arr;
use function Tempest\Support\str;

final class ViewComponentElement implements Element, WithToken
{
    use IsElement;

    private ImmutableArray $dataAttributes;

    private ImmutableArray $expressionAttributes;

    private ImmutableArray $scopedVariables;

    private ImmutableArray $viewComponentAttributes;

    public function __construct(
        public readonly Token $token,
        private readonly Environment $environment,
        private readonly TempestViewCompiler $compiler,
        private readonly ViewComponent $viewComponent,
        array $attributes,
    ) {
        $this->attributes = $attributes;
        $this->viewComponentAttributes = arr($attributes);

        $this->dataAttributes = arr($attributes)
            ->filter(fn (string $_, string $key) => ! str_starts_with($key, ':'))
            ->mapWithKeys(fn (string $value, string $key) => yield str($key)->camel()->toString() => $value);

        $this->expressionAttributes = arr($attributes)
            ->filter(fn (string $_, string $key) => str_starts_with($key, ':'))
            ->filter(fn (string $_, string $key) => ! in_array($key, [':if', ':else', ':elseif', ':foreach', ':forelse'], strict: true))
            ->mapWithKeys(fn (string $value, string $key) => yield str($key)->camel()->ltrim(':')->toString() => $value ?: 'true');

        $this->scopedVariables = arr();
    }

    public function addVariable(string $name): self
    {
        $name = str($name)->trim()->trim('$')->toString();

        $this->scopedVariables[$name] = $name;

        return $this;
    }

    public function getViewComponent(): ViewComponent
    {
        return $this->viewComponent;
    }

    /** @return ImmutableArray<array-key, Slot> */
    public function getSlots(): ImmutableArray
    {
        $slots = arr();

        $defaultTokens = [];

        foreach ($this->token->children as $child) {
            if ($child->tag === 'x-slot') {
                $slot = Slot::named($child);

                $slots[$slot->name] = $slot;
            } else {
                $defaultTokens[] = $child;
            }
        }

        $slots[Slot::DEFAULT] = Slot::default(...$defaultTokens);

        return $slots;
    }

    public function compile(): string
    {
        $slots = $this->getSlots();

        $compiled = str($this->viewComponent->contents);

        // Fallthrough attributes
        $compiled = $compiled
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

        // Add scoped variables
        $compiled = $compiled
            ->prepend(
                // Open the current scope
                sprintf(
                    '<?php (function ($attributes, $slots, $scopedVariables %s %s) { extract($scopedVariables, EXTR_SKIP); ?>',
                    $this->dataAttributes->isNotEmpty() ? ', ' . $this->dataAttributes->map(fn (string $_value, string $key) => "\${$key}")->implode(', ') : '',
                    $this->expressionAttributes->isNotEmpty() ? ', ' . $this->expressionAttributes->map(fn (string $_value, string $key) => "\${$key}")->implode(', ') : '',
                    $this->scopedVariables->isNotEmpty() ? ', ' . $this->scopedVariables->map(fn (string $name) => "\${$name}")->implode(', ') : '',
                ),
            )
            ->append(
                // Close and call the current scope
                sprintf(
                    '<?php })(attributes: %s, slots: %s, scopedVariables: [%s] + ($scopedVariables ?? $this->currentView?->data ?? []) %s %s) ?>',
                    ViewObjectExporter::export($this->viewComponentAttributes),
                    ViewObjectExporter::export($slots),
                    $this->scopedVariables->isNotEmpty()
                        ? $this->scopedVariables->map(fn (string $name) => "'{$name}' => \${$name}")->implode(', ')
                        : '',
                    $this->dataAttributes->isNotEmpty()
                        ? ', ' . $this->dataAttributes->map(fn (mixed $value, string $key) => "{$key}: " . ViewObjectExporter::exportValue($value))->implode(', ')
                        : '',
                    $this->expressionAttributes->isNotEmpty()
                        ? ', ' . $this->expressionAttributes->map(fn (mixed $value, string $key) => "{$key}: " . $value)->implode(', ')
                        : '',
                ),
            );

        // Compile slots
        $compiled = $compiled->replaceRegex(
            regex: '/<x-slot\s*(name="(?<name>[\w-]+)")?((\s*\/>)|>(?<default>(.|\n)*?)<\/x-slot>)/',
            replace: function ($matches) use ($slots) {
                $name = $matches['name'] ?: Slot::DEFAULT;

                $slot = $slots[$name] ?? null;

                $default = $matches['default'] ?? null;

                if ($slot === null) {
                    if ($default) {
                        // There's no slot, but there's a default value in the view component
                        return $default;
                    }

                    // A slot doesn't have any content, so we'll comment it out.
                    // This is to prevent DOM parsing errors (slots in <head> tags is one example, see #937)
                    return $this->environment->isLocal() ? '<!--' . $matches[0] . '-->' : '';
                }

                $slotElement = $this->getSlotElement($slot->name);

                $compiled = $slotElement?->compile() ?? '';

                // There's no default slot content, but there's a default value in the view component
                if (trim($compiled) === '') {
                    return $default;
                }

                return $compiled;
            },
        );

        return $this->compiler->compile($compiled->toString());
    }

    private function getSlotElement(string $name): SlotElement|CollectionElement|null
    {
        $defaultElements = [];

        foreach ($this->getChildren() as $childElement) {
            if ($childElement instanceof SlotElement && $childElement->name === $name) {
                return $childElement;
            }

            if (! $childElement instanceof SlotElement) {
                $defaultElements[] = $childElement;
            }
        }

        if ($name === Slot::DEFAULT) {
            return new CollectionElement($defaultElements);
        }

        return null;
    }
}
