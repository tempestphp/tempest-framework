<?php

namespace Tempest\Internationalization\MessageFormat\Parser;

use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody\ComplexBody;
use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody\Matcher;
use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody\SimplePatternBody;
use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody\Variant;
use Tempest\Internationalization\MessageFormat\Parser\Node\ComplexMessage;
use Tempest\Internationalization\MessageFormat\Parser\Node\Declaration\InputDeclaration;
use Tempest\Internationalization\MessageFormat\Parser\Node\Declaration\LocalDeclaration;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\Attribute;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\Expression;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\FunctionCall;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\FunctionExpression;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\LiteralExpression;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\Option;
use Tempest\Internationalization\MessageFormat\Parser\Node\Expression\VariableExpression;
use Tempest\Internationalization\MessageFormat\Parser\Node\Identifier;
use Tempest\Internationalization\MessageFormat\Parser\Node\Key\WildcardKey;
use Tempest\Internationalization\MessageFormat\Parser\Node\Literal\Literal;
use Tempest\Internationalization\MessageFormat\Parser\Node\Literal\QuotedLiteral;
use Tempest\Internationalization\MessageFormat\Parser\Node\Literal\UnquotedLiteral;
use Tempest\Internationalization\MessageFormat\Parser\Node\Markup\Markup;
use Tempest\Internationalization\MessageFormat\Parser\Node\Markup\MarkupType;
use Tempest\Internationalization\MessageFormat\Parser\Node\MessageNode;
use Tempest\Internationalization\MessageFormat\Parser\Node\ParsingException;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Pattern;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Placeholder;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\QuotedPattern;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Text;
use Tempest\Internationalization\MessageFormat\Parser\Node\SimpleMessage;
use Tempest\Internationalization\MessageFormat\Parser\Node\Variable;

final class Parser
{
    private string $input;
    private int $pos = 0;
    private int $len;

    /**
     * Regex for a valid name-start character, as per ABNF.
     * `\p{L}`: any Unicode letter.
     */
    private const NAME_START_REGEX = '/[a-zA-Z_+]|\p{L}/u';

    /**
     * Regex for valid subsequent name characters.
     * `\p{N}`: any Unicode number.
     */
    private const NAME_CHAR_REGEX = '/[a-zA-Z0-9_.-]|\p{L}|\p{N}/u';

    public function __construct(string $input)
    {
        $this->input = str_replace("\r\n", "\n", $input);
        $this->len = mb_strlen($this->input, 'UTF-8');
    }

    /**
     * Parses the input string and returns the root MessageNode.
     */
    public function parse(): MessageNode
    {
        $this->consumeOptionalWhitespace();

        $peek = $this->peek(6);

        if (str_starts_with($peek, '.local') || str_starts_with($peek, '.input') || str_starts_with($peek, '.match')) {
            return $this->parseComplexMessage();
        }

        if (str_starts_with($peek, '{{')) {
            return $this->parseComplexMessage();
        }

        $message = $this->parseSimpleMessage();
        $this->consumeOptionalWhitespace();

        if (! $this->isEof()) {
            $this->throw("Expected end of input but found '{$this->peek()}'");
        }

        return $message;
    }

    private function parseSimpleMessage(): SimpleMessage
    {
        $pattern = $this->parsePattern();

        return new SimpleMessage($pattern);
    }

    private function parseComplexMessage(): ComplexMessage
    {
        $declarations = [];

        while (! $this->isEof()) {
            $snapshot = $this->pos;
            $this->consumeOptionalWhitespace();
            $peek = $this->peek(6);

            if (str_starts_with($peek, '.input')) {
                $declarations[] = $this->parseInputDeclaration();
            } elseif (str_starts_with($peek, '.local')) {
                $declarations[] = $this->parseLocalDeclaration();
            } else {
                $this->pos = $snapshot;
                break;
            }
        }

        $this->consumeOptionalWhitespace();
        $body = $this->parseComplexBody();
        $this->consumeOptionalWhitespace();

        if (! $this->isEof()) {
            $this->throw("Expected end of input but found '{$this->peek()}'");
        }

        return new ComplexMessage($declarations, $body);
    }

    private function parseInputDeclaration(): InputDeclaration
    {
        $this->consumeKeyword('.input');
        $this->consumeRequiredWhitespace();

        return new InputDeclaration($this->parseVariableExpression());
    }

    private function parseLocalDeclaration(): LocalDeclaration
    {
        $this->consumeKeyword('.local');
        $this->consumeRequiredWhitespace();

        $variable = $this->parseVariable();

        $this->consumeOptionalWhitespace();
        $this->consumeChar('=');
        $this->consumeOptionalWhitespace();

        return new LocalDeclaration($variable, $this->parseExpression());
    }

    private function parseComplexBody(): ComplexBody
    {
        if ($this->peek(2) === '{{') {
            return $this->parseQuotedPattern();
        }

        if ($this->peek(6) === '.match') {
            return $this->parseMatcher();
        }

        $pattern = $this->parsePattern();

        return new SimplePatternBody($pattern);
    }

    private function parseMatcher(): Matcher
    {
        $this->consumeKeyword('.match');
        $selectors = [];

        do {
            $this->consumeRequiredWhitespace();

            $selectors[] = $this->parseVariable();
            $snapshot = $this->pos;

            $this->consumeOptionalWhitespace();

            $continue = $this->peek() === '$';
            $this->pos = $snapshot;
        } while ($continue);

        $this->consumeRequiredWhitespace();

        $variants = [];
        $variants[] = $this->parseVariant();

        while (true) {
            $snapshot = $this->pos;
            $this->consumeOptionalWhitespace();
            $peek = $this->peek();

            if ($peek === '' || ! ($peek === '*' || $peek === '|' || preg_match(self::NAME_CHAR_REGEX, $peek))) {
                $this->pos = $snapshot;
                break;
            }

            $variants[] = $this->parseVariant();
        }

        return new Matcher($selectors, $variants);
    }

    private function parseVariant(): Variant
    {
        $keys = [];

        do {
            if ($this->peek() === '*') {
                $this->consumeChar('*');
                $keys[] = new WildcardKey();
            } else {
                $keys[] = $this->parseLiteral();
            }
            $this->consumeOptionalWhitespace();
        } while ($this->peek() !== '{');

        return new Variant($keys, $this->parseQuotedPattern());
    }

    private function parsePattern(string $terminator = ''): Pattern
    {
        $elements = [];
        $buffer = '';
        $terminatorLen = strlen($terminator);

        while (! $this->isEof()) {
            if ($terminatorLen > 0 && $this->peek($terminatorLen) === $terminator) {
                break;
            }

            $char = $this->peek();

            if ($char === '{') {
                if ($buffer !== '') {
                    $elements[] = new Text($buffer);
                    $buffer = '';
                }

                $elements[] = $this->parsePlaceholder();

                continue;
            }

            if ($char === '}') {
                $this->throw("Unmatched '}' in pattern");
            }

            if ($char === '\\') {
                $buffer .= $this->parseEscapedChar(['{', '}', '|', '\\']);
            } else {
                $buffer .= $this->readChar();
            }
        }

        if ($buffer !== '') {
            $elements[] = new Text($buffer);
        }

        return new Pattern($elements);
    }

    private function parseQuotedPattern(): QuotedPattern
    {
        $this->consumeChar('{');
        $this->consumeChar('{');
        $pattern = $this->parsePattern('}}');
        $this->consumeChar('}');
        $this->consumeChar('}');

        return new QuotedPattern($pattern);
    }

    private function parsePlaceholder(): Placeholder
    {
        $peek = $this->peek(2);

        if ($peek === '{#' || $peek === '{/') {
            return $this->parseMarkup();
        }

        return $this->parseExpression();
    }

    private function parseExpression(): Expression
    {
        $this->consumeChar('{');
        $this->consumeOptionalWhitespace();

        $node = $this->parseExpressionBody();

        $this->consumeOptionalWhitespace();
        $this->consumeChar('}');

        return $node;
    }

    private function parseVariableExpression(): VariableExpression
    {
        $this->consumeChar('{');
        $this->consumeOptionalWhitespace();
        $variable = $this->parseVariable();
        $this->consumeOptionalWhitespace();
        $function = $this->peek() === ':' ? $this->parseFunction() : null;
        $attributes = [];

        while ($this->peek() === '@') {
            $attributes[] = $this->parseAttribute();
            $this->consumeOptionalWhitespace();
        }

        $this->consumeOptionalWhitespace();
        $this->consumeChar('}');

        return new VariableExpression($variable, $function, $attributes);
    }

    private function parseExpressionBody(): Expression
    {
        $subject = null;
        $function = null;

        if ($this->peek() === '$') {
            $subject = $this->parseVariable();
        } elseif ($this->peek() === ':') {
            $function = $this->parseFunction();
        } else {
            $subject = $this->parseLiteral();
        }

        $this->consumeOptionalWhitespace();

        if ($function === null && $this->peek() === ':') {
            $function = $this->parseFunction();
        }

        $attributes = [];
        while ($this->peek() === '@') {
            $attributes[] = $this->parseAttribute();
            $this->consumeOptionalWhitespace();
        }

        if ($subject instanceof Variable) {
            return new VariableExpression($subject, $function, $attributes);
        }

        if ($subject instanceof Literal) {
            return new LiteralExpression($subject, $function, $attributes);
        }

        if ($function !== null) {
            return new FunctionExpression($function, $attributes);
        }

        $this->throw('Invalid expression structure.');
    }

    private function parseMarkup(): Markup
    {
        $this->consumeChar('{');
        $this->consumeOptionalWhitespace();

        $type = MarkupType::OPEN;

        if ($this->peek() === '/') {
            $this->consumeChar('/');
            $type = MarkupType::CLOSE;
        } else {
            $this->consumeChar('#');
        }

        $identifier = $this->parseIdentifier();
        $this->consumeOptionalWhitespace();

        $options = [];
        while ($this->isEof() === false && ! in_array($this->peek(), ['@', '/', '}'], true)) {
            $options[] = $this->parseOption();
            $this->consumeOptionalWhitespace();
        }

        $attributes = [];
        while ($this->peek() === '@') {
            $attributes[] = $this->parseAttribute();
            $this->consumeOptionalWhitespace();
        }

        if ($type === MarkupType::OPEN && $this->peek() === '/') {
            $this->consumeChar('/');
            $type = MarkupType::STANDALONE;
        }

        $this->consumeOptionalWhitespace();
        $this->consumeChar('}');

        return new Markup($type, $identifier, $options, $attributes);
    }

    private function parseFunction(): FunctionCall
    {
        $this->consumeChar(':');
        $identifier = $this->parseIdentifier();
        $this->consumeOptionalWhitespace();

        $options = [];
        while ($this->isEof() === false && ! in_array($this->peek(), ['@', '}'], true)) {
            $options[] = $this->parseOption();
            $this->consumeOptionalWhitespace();
        }

        return new FunctionCall($identifier, $options);
    }

    private function parseOption(): Option
    {
        $identifier = $this->parseIdentifier();

        $this->consumeOptionalWhitespace();
        $this->consumeChar('=');
        $this->consumeOptionalWhitespace();

        $value = $this->peek() === '$' ? $this->parseVariable() : $this->parseLiteral();

        return new Option($identifier, $value);
    }

    private function parseAttribute(): Attribute
    {
        $this->consumeChar('@');
        $identifier = $this->parseIdentifier();
        $this->consumeOptionalWhitespace();
        $value = null;

        if ($this->peek() === '=') {
            $this->consumeChar('=');
            $this->consumeOptionalWhitespace();

            $value = $this->parseLiteral();
        }

        return new Attribute($identifier, $value);
    }

    private function parseVariable(): Variable
    {
        $this->consumeChar('$');

        return new Variable($this->parseIdentifier());
    }

    private function parseLiteral(): Literal
    {
        if ($this->peek() === '|') {
            return $this->parseQuotedLiteral();
        }

        return $this->parseUnquotedLiteral();
    }

    private function parseQuotedLiteral(): QuotedLiteral
    {
        $this->consumeChar('|');
        $buffer = '';

        while (! $this->isEof() && $this->peek() !== '|') {
            if ($this->peek() === '\\') {
                $buffer .= $this->parseEscapedChar(['|', '\\']);
            } else {
                $buffer .= $this->readChar();
            }
        }

        $this->consumeChar('|');

        return new QuotedLiteral($buffer);
    }

    private function parseUnquotedLiteral(): UnquotedLiteral
    {
        $buffer = '';
        $char = $this->peek();

        if ($char === '' || ! preg_match(self::NAME_CHAR_REGEX, $char)) {
            $this->throw("Invalid unquoted literal start character: '$char'");
        }

        $buffer .= $this->readChar();

        while (! $this->isEof()) {
            $char = $this->peek();

            if (! preg_match(self::NAME_CHAR_REGEX, $char)) {
                break;
            }

            $buffer .= $this->readChar();
        }

        return new UnquotedLiteral($buffer);
    }

    private function parseIdentifier(): Identifier
    {
        $name = $this->parseName();

        if ($this->peek() === ':') {
            $this->consumeChar(':');
            $namespace = $name;
            $name = $this->parseName();

            return new Identifier($name, $namespace);
        }

        return new Identifier($name);
    }

    private function parseName(): string
    {
        $start = $this->peek();

        if (! preg_match(self::NAME_START_REGEX, $start)) {
            $this->throw("Invalid identifier start character: '$start'");
        }

        $buffer = $this->readChar();

        while (! $this->isEof()) {
            $char = $this->peek();

            if (! preg_match(self::NAME_CHAR_REGEX, $char)) {
                break;
            }

            $buffer .= $this->readChar();
        }

        return $buffer;
    }

    private function parseEscapedChar(array $escapable): string
    {
        $this->consumeChar('\\');
        $char = $this->peek();

        if (in_array($char, $escapable, true)) {
            return $this->readChar();
        }

        $this->throw("Invalid escape sequence: \\{$char}.");
    }

    private function consumeKeyword(string $keyword): void
    {
        if ($this->peek(strlen($keyword)) !== $keyword) {
            $this->throw("Expected keyword '$keyword'");
        }

        $this->pos += strlen($keyword);
    }

    private function consumeChar(string $expected): void
    {
        if ($this->isEof()) {
            $this->throw("Expected `{$expected}` but reached end of input.");
        }

        $char = $this->readChar();

        if ($char !== $expected) {
            $this->pos--;
            $this->throw("Expected `{$expected}` but found `{$char}`.");
        }
    }

    private function consumeOptionalWhitespace(): void
    {
        $this->consumeWhitespace(false);
    }

    private function consumeRequiredWhitespace(): void
    {
        $this->consumeWhitespace(true);
    }

    private function consumeWhitespace(bool $required): void
    {
        $startPos = $this->pos;

        while (! $this->isEof()) {
            $char = $this->peek();

            if (! preg_match('/^[\s\x{061C}\x{200E}\x{200F}\x{2066}-\x{2069}]/u', $char)) {
                break;
            }

            $this->readChar();
        }

        if ($required && $this->pos === $startPos) {
            $this->throw('Required whitespace not found.');
        }
    }

    private function readChar(): string
    {
        if ($this->isEof()) {
            return '';
        }

        $char = mb_substr($this->input, $this->pos, 1, 'UTF-8');
        $this->pos++;

        return $char;
    }

    private function peek(int $length = 1): string
    {
        if ($this->isEof()) {
            return '';
        }

        return mb_substr($this->input, $this->pos, $length, 'UTF-8');
    }

    private function isEof(): bool
    {
        return $this->pos >= $this->len;
    }

    private function throw(string $message): never
    {
        throw new ParsingException($message, $this->pos);
    }
}
