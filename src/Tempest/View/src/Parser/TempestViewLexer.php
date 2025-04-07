<?php

namespace Tempest\View\Parser;

final class TempestViewLexer
{
    private int $position = 0;

    private ?string $current;

    public function __construct(
        private readonly string $html,
    ) {
        $this->current = $this->html[$this->position] ?? null;
    }

    public function lex(): TokenCollection
    {
        $tokens = [];

        while ($this->current) {
            if ($this->comesNext('<?')) {
                $tokens[] = $this->lexPhp();
            } elseif ($this->comesNext('<!--')) {
                $tokens[] = $this->lexComment();
            } elseif ($this->comesNext('<!doctype') || $this->comesNext('<!DOCTYPE')) {
                $tokens[] = $this->lexDocType();
            } elseif ($this->comesNext('<')) {
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
        $offset = strspn($this->html, "\t " . PHP_EOL, $this->position);

        return $this->seek(length: $length, offset: $offset);
    }

    private function consume(int $length = 1): string
    {
        $buffer = substr($this->html, $this->position, $length);
        $this->position += $length;
        $this->current = $this->html[$this->position] ?? null;

        return $buffer;
    }

    private function consumeUntil(string $stopAt): string
    {
        $offset = strcspn($this->html, $stopAt, $this->position);

        return $this->consume($offset);
    }

    private function consumeWhile(string $continueWhile): string
    {
        $offset = strspn($this->html, $continueWhile, $this->position);

        return $this->consume($offset);
    }

    private function consumeIncluding(string $search): string
    {
        return $this->consumeUntil($search) . $this->consume(strlen($search));
    }

    private function lexTag(): array
    {
        $tag = $this->consumeWhile('</0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_-:');

        $tokens = [];

        if (substr($tag, 1, 1) === '/') {
            $tag .= $this->consumeIncluding('>');
            $tokens[] = new Token($tag, TokenType::CLOSING_TAG);
        } elseif ($this->seekIgnoringWhitespace() === '/' || str_ends_with($tag, '/')) {
            $tag .= $this->consumeIncluding('>');
            $tokens[] = new Token($tag, TokenType::SELF_CLOSING_TAG);
        } else {
            $tokens[] = new Token($tag, TokenType::OPEN_TAG_START);

            while ($this->seek() !== null && $this->seekIgnoringWhitespace() !== '>' && $this->seekIgnoringWhitespace() !== '/') {
                if ($this->seekIgnoringWhitespace(2) === '<?') {
                    $tokens[] = $this->lexPhp();
                    continue;
                }

                $attributeName = $this->consumeWhile("\n ");

                $attributeName .= $this->consumeUntil('= >');

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
        $buffer = '';

        while ($this->seek(2) !== '?>') {
            $buffer .= $this->consume();
        }

        $buffer .= $this->consume(2);

        return new Token($buffer, TokenType::PHP);
    }

    private function lexContent(): Token
    {
        $buffer = $this->consumeUntil('<');

        return new Token($buffer, TokenType::CONTENT);
    }

    private function lexComment(): Token
    {
        $buffer = '';

        while ($this->seek(3) !== '-->') {
            $buffer .= $this->consume();
        }

        $buffer .= $this->consume(3);

        return new Token($buffer, TokenType::COMMENT);
    }

    private function lexDoctype(): Token
    {
        $buffer = $this->consumeIncluding('>');

        return new Token($buffer, TokenType::DOCTYPE);
    }
}
