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
use Tempest\View\Parser\TokenType;
use Tempest\View\Slot;
use Tempest\View\ViewCache;
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

    private ?ImmutableArray $slots = null;

    public function __construct(
        public readonly Token $token,
        private readonly Environment $environment,
        private readonly TempestViewCompiler $compiler,
        private readonly ViewCache $viewCache,
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
        if ($this->slots !== null) {
            return $this->slots;
        }

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

        $this->slots = $slots;

        return $this->slots;
    }

    public function compile(): string
    {
        $slots = $this->getSlots();

        $compiled = $this
            ->compileComponent()
            ->prepend(
                sprintf(
                    '<?php return function ($attributes, $slots, $scopedVariables %s %s %s) { extract($scopedVariables, EXTR_SKIP); ?>',
                    $this->dataAttributes->isNotEmpty() ? ', ' . $this->dataAttributes->map(fn (string $_value, string $key) => "\${$key}")->implode(', ') : '',
                    $this->expressionAttributes->isNotEmpty() ? ', ' . $this->expressionAttributes->map(fn (string $_value, string $key) => "\${$key}")->implode(', ') : '',
                    $this->scopedVariables->isNotEmpty() ? ', ' . $this->scopedVariables->map(fn (string $name) => "\${$name}")->implode(', ') : '',
                ),
            )
            ->append('<?php };');

        $compiledView = $this->compiler->compileWithSourceMap(
            $compiled->toString(),
            sourcePath: $this->viewComponent->file,
            prependImports: $this->getImports(),
        );

        $cacheKey = sprintf('%s:%s', $this->viewComponent->file, hash('xxh64', $compiledView->content));

        $cachePath = $this->viewCache->getCachedViewPath(
            $cacheKey,
            fn () => $compiledView->content,
        );

        $this->viewCache->saveSourceMap($cachePath, $compiledView->sourcePath, $compiledView->lineMap);

        return sprintf(
            '<?php $this->includeViewComponent(%1$s)(attributes: %2$s, slots: %3$s, scopedVariables: [%4$s] + ($scopedVariables ?? $this->currentView?->data ?? []) %5$s %6$s %7$s); ?>',
            var_export($cachePath, true),
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
        );
    }

    private function compileComponent(): ImmutableString
    {
        $tokens = TempestViewParser::ast($this->viewComponent->contents);

        $buffer = '';

        /**
         * @var int $i
         * @var Token $token
         */
        foreach ($tokens as $i => $token) {
            // Fallthrough attributes will be applied to the first element in the component.
            $shouldApplyFallthrough = $i === 0 && $token->type === TokenType::OPEN_TAG_START && $token->tag !== 'x-slot';

            if (! $shouldApplyFallthrough) {
                // Anything else is is compiled like normal
                $buffer .= $this->compileTokens(
                    tokens: [$token],
                );

                continue;
            }

            $attributes = arr($token->htmlAttributes)
                ->map(fn (string $value) => new MutableString($value));

            // class, style, and id are fallthrough attributes
            $attributes = $this->applyFallthroughAttribute($attributes, 'class');
            $attributes = $this->applyFallthroughAttribute($attributes, 'style');
            $attributes = $this->applyFallthroughAttribute($attributes, 'id');

            $attributeString = $attributes
                ->map(fn (MutableString $value, string $key) => sprintf('%s="%s"', $key, $value->trim()))
                ->implode(' ')
                ->when(
                    fn (ImmutableString $s) => $s->isNotEmpty(),
                    fn (ImmutableString $s) => $s->prepend(' '),
                );

            $buffer .= sprintf('<%s%s>', $token->tag, $attributeString);

            $buffer .= $this->compileTokens(
                tokens: $token->children,
                parentToken: $token,
            );

            if ($token->closingToken?->type !== TokenType::SELF_CLOSING_TAG_END) {
                $buffer .= $token->closingToken?->compile();
            }
        }

        return str($buffer);
    }

    private function compileTokens(iterable $tokens, ?Token $parentToken = null): string
    {
        $buffer = '';

        $parentIsComponent = $parentToken !== null && $this->isViewComponentToken($parentToken);

        foreach ($tokens as $token) {
            if ($token->tag === 'x-slot') {
                // Preserve named slots inside child components: they are outgoing slot-fillers for that child.
                if ($parentIsComponent && $token->getAttribute('name') !== null) {
                    $buffer .= $this->compileRegularOpeningTag($token);
                    $buffer .= $this->compileTokens(tokens: $token->children, parentToken: $token);
                    $buffer .= $token->closingToken?->compile();
                } else {
                    $buffer .= $this->compileSlotToken($token);
                }

                continue;
            }

            if ($token->type !== TokenType::OPEN_TAG_START) {
                $buffer .= $token->compile();
                continue;
            }

            $buffer .= $this->compileRegularOpeningTag($token);
            $buffer .= $this->compileTokens(tokens: $token->children, parentToken: $token);
            $buffer .= $token->closingToken?->compile();
        }

        return $buffer;
    }

    private function compileRegularOpeningTag(Token $token): string
    {
        return $token->content . $token->compileAttributes() . $token->endingToken?->compile();
    }

    private function compileSlotToken(Token $slotToken): string
    {
        $slots = $this->getSlots();
        $name = $this->resolveSlotName($slotToken);
        $slot = $slots[$name] ?? null;
        $default = $slotToken->compileChildren();

        if ($slot === null) {
            if ($default !== '') {
                // There's no slot, but there's a default value in the view component
                return $default;
            }

            // A slot doesn't have any content, so we'll comment it out.
            // This is to prevent DOM parsing errors (slots in <head> tags is one example, see #937)
            return $this->environment->isLocal() ? '<!--' . $slotToken->compile() . '-->' : '';
        }

        $slotElement = $this->getSlotElement($slot->name);

        if ($slotElement === null) {
            return $default;
        }

        $compiled = $this->compiler->compileElement($slotElement);

        // There's no default slot content, but there's a default value in the view component
        if (trim($compiled) === '') {
            return $default;
        }

        return $compiled;
    }

    private function resolveSlotName(Token $slotToken): string
    {
        $name = $slotToken->getAttribute('name');

        if ($name !== null && $name !== '') {
            return $name;
        }

        if (preg_match('/\sname\s*=\s*"(?<name>[\w-]+)"/', $slotToken->content, $matches) === 1) {
            return $matches['name'];
        }

        if (preg_match("/\sname\s*=\s*'(?<name>[\\w-]+)'/", $slotToken->content, $matches) === 1) {
            return $matches['name'];
        }

        return Slot::DEFAULT;
    }

    private function isViewComponentToken(Token $token): bool
    {
        if ($token->tag === null) {
            return false;
        }

        if (! str_starts_with($token->tag, 'x-')) {
            return false;
        }

        return $token->tag !== 'x-slot';
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
                ? sprintf("'%s' => %s", $key, $value ?: 'true')
                : sprintf("'%s' => %s", $key, ViewObjectExporter::exportValue($value));
        }

        return sprintf('new \%s([%s])', ImmutableArray::class, implode(', ', $entries));
    }

    public function getImports(): array
    {
        $imports = [];

        if ($this->parent instanceof Element) {
            $imports = [...$imports, ...$this->parent->getImports()];
        }

        foreach ($this->getChildren() as $child) {
            if (! $child instanceof PhpElement) {
                continue;
            }

            $imports = [...$imports, ...$child->getImports()];
        }

        return $imports;
    }
}
