<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\Core\Environment;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Str\ImmutableString;
use Tempest\Support\Str\MutableString;
use Tempest\View\Attributes\ApplyAttribute;
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

    // Part of the implementation of the :apply attribute on ViewComponentElement
    private ?string $applyExpression = null;

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
            ->mapWithKeys(fn (string $value, string $key) => yield str($key)->camel()->ltrim(':')->toString() => $value === '' ? 'true' : $value);

        $this->scopedVariables = arr();
    }

    public function addVariable(string $name): self
    {
        $name = str($name)->trim()->trim('$')->toString();

        $this->scopedVariables[$name] = $name;

        return $this;
    }

    /**
     * Called by ApplyAttribute when :apply="..." targets this element at the call site.
     * Strips 'apply' from the attribute maps so it does not leak through as a component
     * variable or appear in the exported $attributes array passed to the child.
     */
    public function setApplyExpression(string $expression): void
    {
        $this->applyExpression = $expression;

        $filtered = [];
        foreach ($this->viewComponentAttributes as $key => $value) {
            if ($key === 'apply') {
                continue;
            }

            $filtered[$key] = $value;
        }

        $this->viewComponentAttributes = new ImmutableArray($filtered);

        $filtered = [];
        foreach ($this->expressionAttributes as $key => $value) {
            if ($key === 'apply') {
                continue;
            }

            $filtered[$key] = $value;
        }

        $this->expressionAttributes = new ImmutableArray($filtered);
    }

    public function getViewComponent(): ViewComponent
    {
        return $this->viewComponent;
    }

    /** @return ImmutableArray<array-key, Slot> */
    public function getSlots(): ImmutableArray
    {
        if ($this->slots instanceof ImmutableArray) {
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

        $containsApply = static function (iterable $tokens) use (&$containsApply): bool {
            foreach ($tokens as $token) {
                if (array_key_exists(':apply', $token->htmlAttributes) || $containsApply($token->children)) {
                    return true;
                }
            }

            return false;
        };

        // If the component template itself uses :apply on any element, the developer is controlling
        // attribute spreading manually. If :apply was set at the call site, all attributes are
        // forwarded explicitly via the merged $attributes array. Either way, skip auto-fallthrough.
        $skipFallthrough = $this->applyExpression !== null || $containsApply($tokens);

        $buffer = '';

        // Tracks whether we have already applied fallthrough to the first valid target,
        // replacing the old $i === 0 index check which broke whenever a PHP preamble,
        // whitespace token, comment, or doctype preceded the first HTML element.
        $fallthroughApplied = false;

        foreach ($tokens as $token) {
            // A valid fallthrough target is the first real HTML open or self-closing tag
            // that is not x-slot. SELF_CLOSING_TAG covers bare no-attribute forms like <div/>.
            // OPEN_TAG_START covers everything else, including <div class="foo" />.
            $shouldApplyFallthrough =
                ! $fallthroughApplied && ! $skipFallthrough && in_array($token->type, [TokenType::OPEN_TAG_START, TokenType::SELF_CLOSING_TAG], true) && $token->tag !== 'x-slot';

            if (! $shouldApplyFallthrough) {
                $buffer .= $this->compileTokens(tokens: [$token]);
                continue;
            }

            $fallthroughApplied = true;

            $attributes = arr($token->htmlAttributes)
                ->map(fn (string $value) => new MutableString($value));

            foreach (['class', 'style', 'id'] as $name) {
                // If the root element already declares this attribute — in plain form (class="...")
                // or expression form (:class="...") — leave the developer's logic untouched.
                if (array_key_exists($name, $token->htmlAttributes)) {
                    continue;
                }

                if (array_key_exists(':' . $name, $token->htmlAttributes)) {
                    continue;
                }

                $attributes = $this->applyFallthroughAttribute($attributes, $name);
            }

            $attributeString = $attributes
                ->map(fn (MutableString $value, string $key) => sprintf('%s="%s"', $key, $value->trim()))
                ->implode(' ')
                ->when(
                    fn (ImmutableString $s) => $s->isNotEmpty(),
                    fn (ImmutableString $s) => $s->prepend(' '),
                );

            if ($token->type === TokenType::SELF_CLOSING_TAG) {
                // Bare self-closing token with no separate attribute tokens (e.g. <div/>).
                // Reconstruct as a self-closing tag with the merged attributes.
                $buffer .= sprintf('<%s%s />', $token->tag, $attributeString);
                continue;
            }

            // OPEN_TAG_START — identical to the original behaviour.
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

        $parentIsComponent = $parentToken instanceof Token && $this->isViewComponentToken($parentToken);

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
        $define = $slotToken->getAttribute('define');

        if ($define !== null && $define !== '') {
            return $define;
        }

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
                ? sprintf("'%s' => %s", $key, $value === '' ? 'true' : $value)
                : sprintf("'%s' => %s", $key, ViewObjectExporter::exportValue($value));
        }

        $baseArray = sprintf('new \%s([%s])', ImmutableArray::class, implode(', ', $entries));

        if ($this->applyExpression !== null) {
            // Merge the apply expression over the base attribute array at runtime.
            // Right-side wins on key collisions — the passed ImmutableArray or plain array
            // overwrites any matching key already in the base. iterator_to_array handles both.
            return sprintf(
                'new \%1$s(array_replace(iterator_to_array(%2$s), is_array(%3$s) ? %3$s : iterator_to_array(%3$s)))',
                ImmutableArray::class,
                $baseArray,
                $this->applyExpression,
            );
        }

        return $baseArray;
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
