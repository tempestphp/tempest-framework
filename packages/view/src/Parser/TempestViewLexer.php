<?php

namespace Tempest\View\Parser;

final class TempestViewLexer
{
    private const string WHITESPACE = "\r\n\t\f ";

    private int $position = 0;

    private int $line = 1;

    private ?string $current;

    public function __construct(
        private readonly string $html,
        private readonly ?string $sourcePath = null,
    ) {
        $this->current = $this->html[$this->position] ?? null;
    }

    public function lex(): TokenCollection
    {
        $tokens = [];

        while ($this->current !== null) {
            if ($this->comesNext('<?xml', 5)) {
                $tokens[] = $this->lexXml();
            } elseif ($this->comesNext('<?', 2)) {
                $tokens[] = $this->lexPhp();
            } elseif ($this->comesNext('<!--', 4)) {
                $tokens[] = $this->lexComment();
            } elseif ($this->comesNext('<!doctype', 9) || $this->comesNext('<!DOCTYPE', 9)) {
                $tokens[] = $this->lexDocType();
            } elseif ($this->comesNext('<![CDATA', 8)) {
                $tokens = [...$tokens, ...$this->lexCharacterData()];
            } elseif ($this->comesNext('<', 1)) {
                $tokens = [...$tokens, ...$this->lexTag()];
            } elseif (str_contains(self::WHITESPACE, $this->current)) {
                $tokens[] = $this->lexWhitespace();
            } else {
                $tokens[] = $this->lexContent();
            }
        }

        return new TokenCollection($tokens);
    }

    private function comesNext(string $search, ?int $length = null): bool
    {
        $length ??= strlen($search);

        if ($length === 1) {
            return ($this->html[$this->position] ?? null) === $search;
        }

        return substr_compare($this->html, $search, $this->position, $length) === 0;
    }

    private function seek(int $length = 1, int $offset = 0): ?string
    {
        if ($length === 1) {
            return $this->html[$this->position + $offset] ?? null;
        }

        $seek = substr($this->html, $this->position + $offset, $length);

        if ($seek === '') {
            return null;
        }

        return $seek;
    }

    private function seekIgnoringWhitespace(): ?string
    {
        return $this->seek(
            // Whitespace offset
            offset: strspn($this->html, self::WHITESPACE, $this->position),
        );
    }

    private function consume(int $length = 1): string
    {
        if ($length === 0) {
            return '';
        }

        if ($length === 1) {
            $char = $this->html[$this->position++] ?? null;
            $this->current = $this->html[$this->position] ?? null;
            return $char ?? '';
        }

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

    private function makeToken(string $content, TokenType $type, int $line): Token
    {
        return new Token(
            content: $content,
            type: $type,
            line: $line,
            sourcePath: $this->sourcePath,
        );
    }

    private function lexTag(): array
    {
        $tagLine = $this->line;
        $tag = $this->consumeWhile('</0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_-:');

        $tokens = [];

        if (substr($tag, 1, 1) === '/') {
            $tag .= $this->consumeIncluding('>');
            $tokens[] = $this->makeToken($tag, TokenType::CLOSING_TAG, $tagLine);
        } elseif ($this->seekIgnoringWhitespace() === '/' || str_ends_with($tag, '/')) {
            $tag .= $this->consumeIncluding('>');
            $tokens[] = $this->makeToken($tag, TokenType::SELF_CLOSING_TAG, $tagLine);
        } else {
            $tokens[] = $this->makeToken($tag, TokenType::OPEN_TAG_START, $tagLine);

            while ($this->current !== null) {
                $whitespaceOffset = strspn($this->html, self::WHITESPACE, $this->position);
                $next = $this->seek(offset: $whitespaceOffset);

                if ($next === '>' || $next === '/') {
                    break;
                }

                if ($next === '<' && $this->seek(length: 2, offset: $whitespaceOffset) === '<?') {
                    $tokens[] = $this->lexPhp();
                    continue;
                }

                $attributeLine = $this->line;
                $attributeName = $this->consumeWhile(self::WHITESPACE);

                $attributeName .= $this->consumeUntil(self::WHITESPACE . '=/>');

                $hasValue = $this->comesNext('=', 1);

                if ($hasValue) {
                    $attributeName .= $this->consume();
                }

                $tokens[] = $this->makeToken(
                    content: $attributeName,
                    type: TokenType::ATTRIBUTE_NAME,
                    line: $attributeLine,
                );

                if ($hasValue) {
                    $quote = $this->comesNext("'", 1)
                        ? "'"
                        : '"';

                    $attributeValueLine = $this->line;
                    $attributeValue = $this->consumeIncluding($quote);
                    $attributeValue .= $this->consumeIncluding($quote);

                    $tokens[] = $this->makeToken(
                        content: $attributeValue,
                        type: TokenType::ATTRIBUTE_VALUE,
                        line: $attributeValueLine,
                    );
                }
            }

            $next = $this->seek(
                // Whitespace offset
                offset: strspn($this->html, self::WHITESPACE, $this->position),
            );

            if ($next === '>') {
                $openTagEndLine = $this->line;

                $tokens[] = $this->makeToken(
                    content: $this->consumeIncluding('>'),
                    type: TokenType::OPEN_TAG_END,
                    line: $openTagEndLine,
                );
            } elseif ($next === '/') {
                $selfClosingTagEndLine = $this->line;

                $tokens[] = $this->makeToken(
                    content: $this->consumeIncluding('>'),
                    type: TokenType::SELF_CLOSING_TAG_END,
                    line: $selfClosingTagEndLine,
                );
            }
        }

        return $tokens;
    }

    private function lexXml(): Token
    {
        $line = $this->line;
        $buffer = '';

        while (! $this->comesNext('?>', 2) && $this->current !== null) {
            $buffer .= $this->consume();
        }

        $buffer .= $this->consume(2);

        return $this->makeToken($buffer, TokenType::XML, $line);
    }

    private function lexPhp(): Token
    {
        $line = $this->line;
        $buffer = '';

        while (! $this->comesNext('?>', 2) && $this->current !== null) {
            $buffer .= $this->consume();
        }

        $buffer .= $this->consume(2);

        return $this->makeToken($buffer, TokenType::PHP, $line);
    }

    private function lexContent(): Token
    {
        $line = $this->line;
        $buffer = $this->consumeUntil('<');

        return $this->makeToken($buffer, TokenType::CONTENT, $line);
    }

    private function lexComment(): Token
    {
        $line = $this->line;
        $buffer = '';

        while (! $this->comesNext('-->', 3) && $this->current !== null) {
            $buffer .= $this->consume();
        }

        $buffer .= $this->consume(3);

        return $this->makeToken($buffer, TokenType::COMMENT, $line);
    }

    private function lexDoctype(): Token
    {
        $line = $this->line;
        $buffer = $this->consumeIncluding('>');

        return $this->makeToken($buffer, TokenType::DOCTYPE, $line);
    }

    private function lexWhitespace(): Token
    {
        $line = $this->line;
        $buffer = $this->consumeWhile(self::WHITESPACE);

        return $this->makeToken($buffer, TokenType::WHITESPACE, $line);
    }

    private function lexCharacterData(): array
    {
        $characterDataOpenLine = $this->line;

        $tokens = [
            $this->makeToken($this->consumeIncluding('<![CDATA['), TokenType::CHARACTER_DATA_OPEN, $characterDataOpenLine),
        ];

        $buffer = '';

        $contentLine = $this->line;

        while (! $this->comesNext(']]>', 3) && $this->current !== null) {
            $buffer .= $this->consume();
        }

        $tokens[] = $this->makeToken($buffer, TokenType::CONTENT, $contentLine);

        $characterDataCloseLine = $this->line;
        $tokens[] = $this->makeToken($this->consume(3), TokenType::CHARACTER_DATA_CLOSE, $characterDataCloseLine);

        return $tokens;
    }
}
