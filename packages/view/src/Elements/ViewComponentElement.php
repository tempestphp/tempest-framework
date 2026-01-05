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

        $this->viewComponentAttributes = arr($attributes)
            ->mapWithKeys(fn (string $value, string $key) => yield str($key)->ltrim(':')->toString() => $value);

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

        $compiled = $this->applyFallthroughAttributes($compiled);

        $compiled = $compiled
            ->prepend(
                sprintf(
                    '<?php (function ($attributes, $slots, $scopedVariables %s %s %s) { extract($scopedVariables, EXTR_SKIP); ?>',
                    $this->dataAttributes->isNotEmpty() ? ', ' . $this->dataAttributes->map(fn (string $_value, string $key) => "\${$key}")->implode(', ') : '',
                    $this->expressionAttributes->isNotEmpty() ? ', ' . $this->expressionAttributes->map(fn (string $_value, string $key) => "\${$key}")->implode(', ') : '',
                    $this->scopedVariables->isNotEmpty() ? ', ' . $this->scopedVariables->map(fn (string $name) => "\${$name}")->implode(', ') : '',
                ),
            )
            ->append(
                sprintf(
                    '<?php })(attributes: %s, slots: %s, scopedVariables: [%s] + ($scopedVariables ?? $this->currentView?->data ?? []) %s %s %s) ?>',
                    $this->exportAttributesArray(),
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
                    $this->scopedVariables->isNotEmpty()
                        ? ', ' . $this->scopedVariables->map(fn (string $name) => "{$name}: \${$name}")->implode(', ')
                        : '',
                ),
            );

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

    private function applyFallthroughAttributes(ImmutableString $compiled): ImmutableString
    {
        return $compiled->replaceRegex(
            regex: '/^<(?<tag>[\w-]+)(.*?["\s])?>/',
            replace: function (array $matches): string {
                /** @var Token $token */
                $token = TempestViewParser::ast($matches[0])[0];

                $attributes = arr($token->htmlAttributes)
                    ->map(fn (string $value) => new MutableString($value));

                foreach (['class', 'style', 'id'] as $name) {
                    $attributes = $this->applyFallthroughAttribute($attributes, $name);
                }

                $attributeString = $attributes
                    ->map(fn (MutableString $value, string $key) => sprintf('%s="%s"', $key, $value->trim()))
                    ->implode(' ')
                    ->when(
                        fn (ImmutableString $s) => $s->isNotEmpty(),
                        fn (ImmutableString $s) => $s->prepend(' '),
                    );

                return sprintf('<%s%s>', $matches['tag'], $attributeString);
            },
        );
    }

    private function applyFallthroughAttribute(ImmutableArray $attributes, string $name): ImmutableArray
    {
        $hasDataAttribute = isset($this->dataAttributes[$name]);
        $hasExpressionAttribute = isset($this->expressionAttributes[$name]);

        if (! $hasDataAttribute && ! $hasExpressionAttribute) {
            return $attributes;
        }

        $attributes[$name] ??= new MutableString();

        if ($name === 'id') {
            if ($hasDataAttribute) {
                $attributes[$name] = new MutableString($this->dataAttributes[$name]);
            } elseif ($hasExpressionAttribute) {
                $attributes[$name] = new MutableString(sprintf('<?= $%s ?>', $name));
            }
        } else {
            if ($hasDataAttribute) {
                $attributes[$name]->append(' ' . $this->dataAttributes[$name]);
            }
            if ($hasExpressionAttribute) {
                $attributes[$name]->append(sprintf(' <?= $%s ?>', $name));
            }
        }

        return $attributes;
    }

    private function exportAttributesArray(): string
    {
        $entries = [];

        foreach ($this->viewComponentAttributes as $key => $value) {
            $camelKey = str($key)->camel()->toString();
            $isExpression = isset($this->expressionAttributes[$camelKey]);

            $entries[] = $isExpression
                ? sprintf("'%s' => %s", $key, $value)
                : sprintf("'%s' => %s", $key, ViewObjectExporter::exportValue($value));
        }

        return sprintf('new \%s([%s])', ImmutableArray::class, implode(', ', $entries));
    }
}
