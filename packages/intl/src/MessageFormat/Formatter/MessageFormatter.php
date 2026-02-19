<?php

namespace Tempest\Intl\MessageFormat\Formatter;

use Tempest\Intl\MessageFormat\FormattingFunction;
use Tempest\Intl\MessageFormat\MarkupFormatter;
use Tempest\Intl\MessageFormat\Parser\MessageFormatParser;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\ComplexBody;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\Matcher;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\SimplePatternBody;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexMessage;
use Tempest\Intl\MessageFormat\Parser\Node\Declaration\InputDeclaration;
use Tempest\Intl\MessageFormat\Parser\Node\Declaration\LocalDeclaration;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\Expression;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\FunctionCall;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\FunctionExpression;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\LiteralExpression;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\Option;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\VariableExpression;
use Tempest\Intl\MessageFormat\Parser\Node\Key\WildcardKey;
use Tempest\Intl\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Intl\MessageFormat\Parser\Node\Markup\Markup;
use Tempest\Intl\MessageFormat\Parser\Node\Markup\MarkupType;
use Tempest\Intl\MessageFormat\Parser\Node\MessageNode;
use Tempest\Intl\MessageFormat\Parser\Node\ParsingException;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Pattern;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Placeholder;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\QuotedPattern;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Text;
use Tempest\Intl\MessageFormat\Parser\Node\SimpleMessage;
use Tempest\Intl\MessageFormat\Parser\Node\Variable;
use Tempest\Intl\MessageFormat\SelectorFunction;
use Tempest\Intl\MessageFormat\StandaloneMarkupFormatter;

use function Tempest\Support\arr;

final class MessageFormatter
{
    /** @var array<string,LocalVariable> $variables */
    private array $variables = [];

    public function __construct(
        /** @var array<FormattingFunction|SelectorFunction> */
        private readonly array $functions = [],
        /** @var array<MarkupFormatter|StandaloneMarkupFormatter> */
        private readonly array $markupFormatters = [],
    ) {}

    /**
     * Formats a message string with the given variables.
     */
    public function format(string $message, mixed ...$variables): string
    {
        try {
            $ast = new MessageFormatParser($message)->parse();

            $this->variables = $this->parseLocalVariables($variables);

            return $this->formatMessage($ast);
        } catch (ParsingException $e) {
            throw new FormattingException('Failed to parse message.', [
                'message' => $message,
                'variables' => $variables,
                'exception' => $e,
            ]);
        }
    }

    private function formatMessage(MessageNode $message): string
    {
        if ($message instanceof SimpleMessage) {
            return $this->formatPattern($message->pattern);
        }

        if ($message instanceof ComplexMessage) {
            $localVariables = [];

            foreach ($message->declarations as $declaration) {
                if ($declaration instanceof InputDeclaration) {
                    $expression = $declaration->expression;
                    $variableName = $expression->variable->name->name;

                    if (! array_key_exists($variableName, $this->variables)) {
                        if ($declaration->optional) {
                            $parameters = $this->evaluateOptions($expression->function->options ?? []);

                            $this->variables[$variableName] = new LocalVariable(
                                identifier: $variableName,
                                value: $parameters['default'],
                                selector: $this->getSelectorFunction((string) $expression->function?->identifier),
                                formatter: $this->getFormattingFunction((string) $expression->function?->identifier),
                                parameters: $parameters,
                            );
                        } else {
                            throw new FormattingException("Required input variable `{$variableName}` not provided.");
                        }
                    }

                    if ($expression->function instanceof FunctionCall) {
                        $this->variables[$variableName] = new LocalVariable(
                            identifier: $variableName,
                            value: $this->variables[$variableName]->value,
                            selector: $this->getSelectorFunction((string) $expression->function->identifier),
                            formatter: $this->getFormattingFunction((string) $expression->function->identifier),
                            parameters: $this->evaluateOptions($expression->function->options),
                        );
                    }
                } elseif ($declaration instanceof LocalDeclaration) {
                    $variableName = $declaration->variable->name->name;

                    $functionName = $declaration->expression->function
                        ? (string) $declaration->expression->function->identifier
                        : null;

                    $localVariables[$variableName] = new LocalVariable(
                        identifier: $variableName,
                        value: $this->evaluateExpression($declaration->expression)->value,
                        selector: $this->getSelectorFunction($functionName),
                        formatter: $this->getFormattingFunction($functionName),
                        parameters: $declaration->expression->attributes,
                    );
                }
            }

            $originalVariables = $this->variables;
            $this->variables = [...$this->variables, ...$localVariables];

            try {
                return $this->formatComplexBody($message->body);
            } finally {
                $this->variables = $originalVariables;
            }
        }

        throw new FormattingException('Unknown message type: ' . get_class($message));
    }

    private function formatComplexBody(ComplexBody $body): string
    {
        if ($body instanceof QuotedPattern) {
            return $this->formatPattern($body->pattern);
        }

        if ($body instanceof SimplePatternBody) {
            return $this->formatPattern($body->pattern);
        }

        if ($body instanceof Matcher) {
            return $this->formatMatcher($body);
        }

        throw new FormattingException('Unknown complex body type: ' . get_class($body));
    }

    private function formatMatcher(Matcher $matcher): string
    {
        $selectorVariables = [];

        foreach ($matcher->selectors as $selector) {
            $variableName = $selector->name->name;

            if (! array_key_exists($variableName, $this->variables)) {
                throw new FormattingException("Selector variable `{$variableName}` not found.");
            }

            $selectorVariables[] = $this->variables[$variableName];
        }

        $bestVariant = null;
        $wildcardVariant = null;

        foreach ($matcher->variants as $variant) {
            if (count($variant->keys) !== count($selectorVariables)) {
                continue;
            }

            $matches = true;
            $hasWildcard = false;

            for ($i = 0; $i < count($variant->keys); $i++) {
                $keyNode = $variant->keys[$i];
                $variable = $selectorVariables[$i];

                if ($keyNode instanceof WildcardKey) {
                    $hasWildcard = true;
                    continue;
                }

                if (! $keyNode instanceof Literal) {
                    $matches = false;
                    break;
                }

                $variantKey = $keyNode->value;
                $isMatch = false;

                if ($variable->selector) {
                    $isMatch = $variable->selector->match($variantKey, $variable->value, $variable->parameters);
                } else {
                    $isMatch = $variable->value === $variantKey;
                }

                if (! $isMatch) {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                if (! $hasWildcard) {
                    $bestVariant = $variant;
                    break;
                } elseif ($wildcardVariant === null) {
                    $wildcardVariant = $variant;
                }
            }
        }

        $selectedVariant = $bestVariant ?? $wildcardVariant;

        if ($selectedVariant === null) {
            $selectorValues = array_column($selectorVariables, 'value');

            // TODO: test this
            throw new FormattingException('No matching variant found for selector values: ' . json_encode($selectorValues));
        }

        return $this->formatPattern($selectedVariant->pattern->pattern);
    }

    private function formatPattern(Pattern $pattern): string
    {
        $result = '';

        foreach ($pattern->elements as $element) {
            if ($element instanceof Text) {
                $result .= $element->value;
            } elseif ($element instanceof Placeholder) {
                $result .= $this->formatPlaceholder($element);
            }
        }

        return $result;
    }

    private function formatPlaceholder(Placeholder $placeholder): string
    {
        if ($placeholder instanceof Expression) {
            $value = $this->evaluateExpression($placeholder);

            return $value->formatted;
        }

        if ($placeholder instanceof Markup) {
            return $this->formatMarkup($placeholder);
        }

        if ($placeholder instanceof QuotedPattern) {
            return $this->formatPattern($placeholder->pattern);
        }

        throw new FormattingException('Unknown placeholder type: ' . get_class($placeholder));
    }

    private function evaluateExpression(Expression $expression): FormattedValue
    {
        $value = null;
        $formattingFunction = null;
        $parameters = [];

        if ($expression instanceof LiteralExpression) {
            $value = $expression->literal->value;
        } elseif ($expression instanceof VariableExpression) {
            $variableName = $expression->variable->name->name;

            if (! array_key_exists($variableName, $this->variables)) {
                throw new FormattingException("Variable `{$variableName}` not found");
            }

            $value = $this->variables[$variableName]->value;
            $formattingFunction = $this->variables[$variableName]->formatter;
            $parameters = $this->variables[$variableName]->parameters;
        } elseif ($expression instanceof FunctionExpression) {
            $value = null; // Function-only expressions start with null
        }

        if ($expression->function !== null) {
            $functionName = (string) $expression->function->identifier;
            $parameters = $this->evaluateOptions($expression->function->options);

            if (is_null($formattingFunction = $this->getFormattingFunction($functionName))) {
                throw new FormattingException("Unknown function `{$functionName}`.");
            }
        }

        if ($formattingFunction) {
            return $formattingFunction->format($value, $parameters);
        }

        return new FormattedValue($value, $value !== null ? (string) $value : '');
    }

    /**
     * @param array<Option> $options
     */
    private function evaluateOptions(array $options): array
    {
        $result = [];

        foreach ($options as $option) {
            $name = $option->identifier->name;

            if ($option->value instanceof Variable) {
                $variableName = $option->value->name->name;

                if (! array_key_exists($variableName, $this->variables)) {
                    throw new FormattingException("Option variable `{$variableName}` not found.");
                }

                $result[$name] = $this->variables[$variableName]->value;
            } elseif ($option->value instanceof Literal) {
                $result[$name] = $option->value->value;
            }
        }

        return $result;
    }

    private function parseLocalVariables(array $variables): array
    {
        $result = [];

        foreach ($variables as $key => $value) {
            if ($value instanceof LocalVariable) {
                $result[$key] = $value;
            } else {
                $result[$key] = new LocalVariable(
                    identifier: $key,
                    value: $value,
                );
            }
        }

        return $result;
    }

    private function formatMarkup(Markup $markup): string
    {
        $tag = (string) $markup->identifier;
        $options = $this->evaluateOptions($markup->options);

        if ($markup->type === MarkupType::STANDALONE) {
            if (is_null($formatter = $this->getStandaloneMarkupFormatter($tag))) {
                return '';
            }

            return $formatter->format($tag, $options);
        }

        if (is_null($formatter = $this->getMarkupFormatter($tag))) {
            return '';
        }

        if ($markup->type === MarkupType::OPEN) {
            return $formatter->formatOpenTag($tag, $options);
        }

        return $formatter->formatCloseTag($tag);
    }

    private function getMarkupFormatter(?string $tag): ?MarkupFormatter
    {
        if (! $tag) {
            return null;
        }

        return arr($this->markupFormatters)
            ->filter(fn (MarkupFormatter|StandaloneMarkupFormatter $fn) => $fn instanceof MarkupFormatter)
            ->first(fn (MarkupFormatter $fn) => $fn->supportsTag($tag));
    }

    private function getStandaloneMarkupFormatter(?string $tag): ?StandaloneMarkupFormatter
    {
        if (! $tag) {
            return null;
        }

        return arr($this->markupFormatters)
            ->filter(fn (MarkupFormatter|StandaloneMarkupFormatter $fn) => $fn instanceof StandaloneMarkupFormatter)
            ->first(fn (StandaloneMarkupFormatter $fn) => $fn->supportsTag($tag));
    }

    private function getSelectorFunction(?string $name): ?SelectorFunction
    {
        if (! $name) {
            return null;
        }

        return arr($this->functions)
            ->filter(fn (FormattingFunction|SelectorFunction $fn) => $fn instanceof SelectorFunction)
            ->first(fn (SelectorFunction $fn) => $fn->name === $name);
    }

    private function getFormattingFunction(?string $name): ?FormattingFunction
    {
        if (! $name) {
            return null;
        }

        return arr($this->functions)
            ->filter(fn (FormattingFunction|SelectorFunction $fn) => $fn instanceof FormattingFunction)
            ->first(fn (FormattingFunction $fn) => $fn->name === $name);
    }
}
