<?php

namespace Tempest\Intl\MessageFormat\Formatter;

use Exception;
use Tempest\Intl\Locale;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\ComplexBody;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\Matcher;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\SimplePatternBody;
use Tempest\Intl\MessageFormat\Parser\Node\ComplexMessage;
use Tempest\Intl\MessageFormat\Parser\Node\Declaration\InputDeclaration;
use Tempest\Intl\MessageFormat\Parser\Node\Declaration\LocalDeclaration;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\Expression;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\FunctionExpression;
use Tempest\Intl\MessageFormat\Parser\Node\Expression\LiteralExpression;
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
use Tempest\Intl\MessageFormat\Parser\Parser;
use Tempest\Intl\PluralRules\PluralRulesMatcher;

final class MessageFormatter
{
    /** @var array<string,mixed> $variables */
    private array $variables = [];

    public function __construct(
        /** @var MessageFormatFunction[] */
        private readonly array $functions = [],
        private readonly PluralRulesMatcher $pluralRules = new PluralRulesMatcher(),
    ) {}

    /**
     * Formats a message string with the given variables.
     */
    public function format(string $message, mixed ...$variables): string
    {
        try {
            $ast = new Parser($message)->parse();

            $this->variables = $variables;

            return $this->formatMessage($ast, $variables);
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
                    $variableName = $declaration->expression->variable->name->name;

                    if (! array_key_exists($variableName, $this->variables)) {
                        throw new FormattingException("Required input variable '{$variableName}' not provided.");
                    }
                } elseif ($declaration instanceof LocalDeclaration) {
                    $variableName = $declaration->variable->name->name;
                    $value = $this->evaluateExpression($declaration->expression);
                    $localVariables[$variableName] = $value->value;
                }
            }

            $originalVariables = $this->variables;
            $this->variables = [...$this->variables, ...$localVariables];

            try {
                $result = $this->formatComplexBody($message->body);
                $this->variables = $originalVariables;

                return $result;
            } catch (Exception $e) {
                $this->variables = $originalVariables;

                throw $e;
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
        $selectorValues = [];

        foreach ($matcher->selectors as $selector) {
            $variableName = $selector->name->name;

            if (! array_key_exists($variableName, $this->variables)) {
                throw new FormattingException("Selector variable '{$variableName}' not found.");
            }

            $selectorValues[] = $this->variables[$variableName];
        }

        // Find the best matching variant
        $bestVariant = null;
        $wildcardVariant = null;

        foreach ($matcher->variants as $variant) {
            if (count($variant->keys) !== count($selectorValues)) {
                continue; // Key count mismatch
            }

            $matches = true;
            $hasWildcard = false;

            for ($i = 0; $i < count($variant->keys); $i++) {
                $key = $variant->keys[$i];
                $selectorValue = $selectorValues[$i];

                if ($key instanceof WildcardKey) {
                    $hasWildcard = true;
                    continue;
                }

                if ($key instanceof Literal) {
                    if (! $this->matchesKey($selectorValue, $key->value)) {
                        $matches = false;
                        break;
                    }
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
            throw new FormattingException('No matching variant found for selector values: ' . json_encode($selectorValues));
        }

        return $this->formatPattern($selectedVariant->pattern->pattern);
    }

    private function matchesKey(mixed $value, string $keyValue): bool
    {
        if (is_numeric($value)) {
            $number = (float) $value;

            if ($keyValue === ((string) $number) || $keyValue === ((string) ((int) $number))) {
                return true;
            }

            if ($keyValue === $this->pluralRules->getPluralCategory(Locale::default(), $number)) {
                return true;
            }
        }

        return ((string) $value) === $keyValue;
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

        if ($expression instanceof LiteralExpression) {
            $value = $expression->literal->value;
        } elseif ($expression instanceof VariableExpression) {
            $variableName = $expression->variable->name->name;

            if (! array_key_exists($variableName, $this->variables)) {
                throw new FormattingException("Variable `{$variableName}` not found");
            }

            $value = $this->variables[$variableName];
        } elseif ($expression instanceof FunctionExpression) {
            $value = null; // Function-only expressions start with null
        }

        if ($expression->function !== null) {
            $functionName = (string) $expression->function->identifier;
            $options = $this->evaluateOptions($expression->function->options);

            if ($function = $this->getFunction($functionName)) {
                return $function->evaluate($value, $options);
            } else {
                throw new FormattingException("Unknown function `{$functionName}`.");
            }
        }

        $formatted = $value !== null ? ((string) $value) : '';

        return new FormattedValue($value, $formatted);
    }

    private function getFunction(string $name): ?MessageFormatFunction
    {
        return array_find(
            array: $this->functions,
            callback: fn (MessageFormatFunction $fn) => $fn->name === $name,
        );
    }

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

                $result[$name] = $this->variables[$variableName];
            } elseif ($option->value instanceof Literal) {
                $result[$name] = $option->value->value;
            }
        }

        return $result;
    }

    private function formatMarkup(Markup $markup): string
    {
        // TODO: more advanced with options
        // built-in HtmlMarkup
        $tag = (string) $markup->identifier;

        return match ($markup->type) {
            MarkupType::OPEN => "<{$tag}>",
            MarkupType::CLOSE => "</{$tag}>",
            MarkupType::STANDALONE => "<{$tag}/>",
            default => '',
        };
    }
}
