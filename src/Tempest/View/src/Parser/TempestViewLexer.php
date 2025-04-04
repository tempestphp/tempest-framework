<?php

namespace Tempest\View\Parser;

use Closure;

final class TempestViewLexer
{
    private int $position = 0;

    private ?string $current {
        get => $this->html[$this->position] ?? null;
    }

    public function __construct(
        private string $html,
    ) {}

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

    private function seekIgnoringWhitespace(): ?string
    {
        $offset = 0;

        while (trim($this->seek(offset: $offset)) === '') {
            $offset += 1;
        }

        return $this->seek(offset: $offset);
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

    private function consumeUntil(Closure $shouldStop): string
    {
        $buffer = '';

        while ($this->current !== null && $shouldStop($this->seek()) === false) {
            $buffer .= $this->consume();
        }

        return $buffer;
    }

    private function consumeWhile(Closure $shouldContinue): string
    {
        return $this->consumeUntil(fn (string $next) => $shouldContinue($next) === false);
    }

    private function advance(): void
    {
        $this->position++;
    }

    private function lexTag(): array
    {
        $tagBuffer = $this->consumeUntil(fn (string $next) => $next === '>' || $next === ' ' || $next === PHP_EOL);

        $tokens = [];

        if (substr($tagBuffer, 1, 1) === '/') {
            $tagBuffer .= $this->consume();
            $tokens[] = new Token($tagBuffer, TokenType::CLOSING_TAG);
        } elseif ($this->seekIgnoringWhitespace() === '/' || str_ends_with($tagBuffer, '/')) {
            $tagBuffer .= $this->consumeUntil(fn (string $next) => $next === '>');
            $tagBuffer .= $this->consume();
            $tokens[] = new Token($tagBuffer, TokenType::SELF_CLOSING_TAG);
        } else {
            $tokens[] = new Token($tagBuffer, TokenType::OPEN_TAG_START);

            while ($this->seek() !== null && $this->seek() !== '>' && $this->seekIgnoringWhitespace() !== '/') {
                $attributeName = $this->consumeWhile(fn (string $next) => $next === ' ' || $next === PHP_EOL);

                $attributeName .= $this->consumeUntil(fn (string $next) => $next === '=' || $next === ' ' || $next === '>');

                $hasValue = $this->seek() === '=';

                if ($hasValue) {
                    $attributeName .= $this->consume();
                }

                $tokens[] = new Token(
                    content: $attributeName,
                    type: TokenType::ATTRIBUTE_NAME,
                );

                if ($hasValue) {
                    $attributeValue = $this->consumeUntil(fn (string $next) => $next === '"');
                    $attributeValue .= $this->consume();
                    $attributeValue .= $this->consumeUntil(fn (string $next) => $next === '"');
                    $attributeValue .= $this->consume();

                    $tokens[] = new Token(
                        content: $attributeValue,
                        type: TokenType::ATTRIBUTE_VALUE,
                    );
                }
            }

            if ($this->seekIgnoringWhitespace() === '>') {
                $tokens[] = new Token(
                    content: $this->consumeUntil(fn (string $next) => $next === '>') . $this->consume(),
                    type: TokenType::OPEN_TAG_END,
                );
            } elseif ($this->seekIgnoringWhitespace() === '/') {
                $tokens[] = new Token(
                    content: $this->consumeUntil(fn (string $next) => $next === '>') . $this->consume(),
                    type: TokenType::SELF_CLOSING_TAG_END,
                );
            }
        }

        return $tokens;
    }

    private function lexPhp(): Token
    {
        $buffer = $this->consumeUntil(fn () => $this->seek(2) === '?>');

        $buffer .= $this->consume(2);

        return new Token($buffer, TokenType::PHP);
    }

    private function lexContent(): Token
    {
        $buffer = $this->consumeUntil(fn () => $this->seek() === '<');

        return new Token($buffer, TokenType::CONTENT);
    }

    private function lexComment(): Token
    {
        $buffer = $this->consumeUntil(fn () => $this->seek(3) === '-->');

        $buffer .= $this->consume(3);

        return new Token($buffer, TokenType::COMMENT);
    }

    private function lexDoctype(): Token
    {
        $buffer = $this->consumeUntil(fn (string $next) => $next === '>');
        $buffer .= $this->consume();

        return new Token($buffer, TokenType::DOCTYPE);
    }
}
