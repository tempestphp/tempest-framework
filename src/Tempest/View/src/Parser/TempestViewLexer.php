<?php

namespace Tempest\View\Parser;

use Closure;

final class TempestViewLexer
{
    private int $position = 0;

    private ?string $current;

    public function __construct(
        private string $html,
    ) {
        $this->current = $this->html[$this->position] ?? null;
    }

    public function lex(): TokenCollection
    {
        $tokens = [];

        while ($this->current !== null) {
            if ($this->seek(2) === '<?') {
                $tokens[] = $this->lexPhp();
            } elseif ($this->seek(4) === '<!--') {
                $tokens[] = $this->lexComment();
            } elseif ($this->comesNext('<!doctype') || $this->comesNext('<!DOCTYPE')) {
                $tokens[] = $this->lexDocType();
            } elseif ($this->seek() === '<') {
                $tokens = [...$tokens, ...$this->lexTag()];
            } else {
                $tokens[] = $this->lexContent();
            }
        }

        return new TokenCollection($tokens);
    }

    private function comesNext(string $search): bool
    {
        return $this->seek(strlen($search)) === $search;
    }

    private function seek(int $length = 1, int $offset = 0): ?string
    {
        $seek = substr($this->html, $this->position + $offset, $length);

        if ($seek === '') {
            return null;
        }

        return $seek;
    }

    private function seekIgnoringWhitespace(int $length = 1): ?string
    {
        $offset = 0;

        while (trim($this->seek(offset: $offset)) === '') {
            $offset += 1;
        }

        return $this->seek(length: $length, offset: $offset);
    }

    private function consume(int $length = 1): string
    {
        $buffer = '';

        while ($length) {
            $buffer .= $this->current;
            $this->advance();
            $length--;
        }

        return $buffer;
    }

    private function consumeUntil(array|string|Closure $shouldStop): string
    {
        // Early checks for string values to optimize performance
        if (is_string($shouldStop)) {
            $found = strpos($this->html, $shouldStop, $this->position);

            if ($found !== false) {
                return $this->consume($found - $this->position);
            }
        } elseif(is_array($shouldStop)) {
            $earliestPosition = null;

            foreach ($shouldStop as $shouldStopEntry) {
                $found = strpos($this->html, $shouldStopEntry, $this->position);

                if (! $found) {
                    continue;
                }

                if ($earliestPosition === null) {
                    $earliestPosition = $found;
                    continue;
                }

                if ($earliestPosition > $found) {
                    $earliestPosition = $found;
                }
            }

            if ($earliestPosition) {
                return $this->consume($earliestPosition - $this->position);
            }
        }

        $buffer = '';

        while ($this->current !== null) {
            if (is_string($shouldStop) && $shouldStop === $this->current) {
                return $buffer;
            } elseif (is_array($shouldStop) && in_array($this->current, $shouldStop)) {
                return $buffer;
            } elseif ($shouldStop instanceof Closure && $shouldStop($this->current)) {
                return $buffer;
            }

            $buffer .= $this->consume();
        }

        return $buffer;
    }

    private function consumeWhile(string|array $shouldContinue): string
    {
        $buffer = '';

        while ($this->current !== null) {
            if (is_string($shouldContinue) && $shouldContinue !== $this->current) {
                return $buffer;
            } elseif (! in_array($this->current, $shouldContinue)) {
                return $buffer;
            }

            $buffer .= $this->consume();
        }

        return $buffer;
    }

    private function consumeIncluding(string $search): string
    {
        return $this->consumeUntil($search) . $this->consume(strlen($search));
    }

    private function advance(int $amount = 1): void
    {
        $this->position += $amount;
        $this->current = $this->html[$this->position] ?? null;
    }

    private function lexTag(): array
    {
        $tagBuffer = $this->consumeUntil([' ', PHP_EOL, '>']);

        $tokens = [];

        if (substr($tagBuffer, 1, 1) === '/') {
            $tagBuffer .= $this->consume();
            $tokens[] = new Token($tagBuffer, TokenType::CLOSING_TAG);
        } elseif ($this->seekIgnoringWhitespace() === '/' || str_ends_with($tagBuffer, '/')) {
            $tagBuffer .= $this->consumeIncluding('>');
            $tokens[] = new Token($tagBuffer, TokenType::SELF_CLOSING_TAG);
        } else {
            $tokens[] = new Token($tagBuffer, TokenType::OPEN_TAG_START);

            while ($this->seek() !== null && $this->seekIgnoringWhitespace() !== '>' && $this->seekIgnoringWhitespace() !== '/') {
                if ($this->seekIgnoringWhitespace(2) === '<?') {
                    $tokens[] = $this->lexPhp();
                    continue;
                }

                $attributeName = $this->consumeWhile([' ', PHP_EOL]);

                $attributeName .= $this->consumeUntil(['=', ' ', '>']);

                $hasValue = $this->seek() === '=';

                if ($hasValue) {
                    $attributeName .= $this->consume();
                }

                $tokens[] = new Token(
                    content: $attributeName,
                    type: TokenType::ATTRIBUTE_NAME,
                );

                if ($hasValue) {
                    $attributeValue = $this->consumeIncluding('"');
                    $attributeValue .= $this->consumeIncluding('"');

                    $tokens[] = new Token(
                        content: $attributeValue,
                        type: TokenType::ATTRIBUTE_VALUE,
                    );
                }
            }

            if ($this->seekIgnoringWhitespace() === '>') {
                $tokens[] = new Token(
                    content: $this->consumeIncluding('>'),
                    type: TokenType::OPEN_TAG_END,
                );
            } elseif ($this->seekIgnoringWhitespace() === '/') {
                $tokens[] = new Token(
                    content: $this->consumeIncluding('>'),
                    type: TokenType::SELF_CLOSING_TAG_END,
                );
            }
        }

        return $tokens;
    }

    private function lexPhp(): Token
    {
        $buffer = $this->consumeIncluding('?>');

        return new Token($buffer, TokenType::PHP);
    }

    private function lexContent(): Token
    {
        $buffer = $this->consumeUntil('<');

        return new Token($buffer, TokenType::CONTENT);
    }

    private function lexComment(): Token
    {
        $buffer = $this->consumeIncluding('-->');

        return new Token($buffer, TokenType::COMMENT);
    }

    private function lexDoctype(): Token
    {
        $buffer = $this->consumeIncluding('>');

        return new Token($buffer, TokenType::DOCTYPE);
    }
}
