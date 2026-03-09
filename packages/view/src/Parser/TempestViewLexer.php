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
            if ($this->comesNext('<?xml')) {
                $tokens[] = $this->lexXml();
            } elseif ($this->comesNext('<?')) {
                $tokens[] = $this->lexPhp();
            } elseif ($this->comesNext('<!--')) {
                $tokens[] = $this->lexComment();
            } elseif ($this->comesNext('<!doctype') || $this->comesNext('<!DOCTYPE')) {
                $tokens[] = $this->lexDocType();
            } elseif ($this->comesNext('<![CDATA')) {
                $tokens = [...$tokens, ...$this->lexCharacterData()];
            } elseif ($this->comesNext('<')) {
                $tokens = [...$tokens, ...$this->lexTag()];
            } elseif (str_contains(self::WHITESPACE, $this->current)) {
                $tokens[] = $this->lexWhitespace();
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
        $offset = strspn($this->html, self::WHITESPACE, $this->position);

        return $this->seek(length: $length, offset: $offset);
    }

    private function consume(int $length = 1): string
    {
        $buffer = substr($this->html, $this->position, $length);
        $this->position += $length;
        $this->line += substr_count($buffer, "\n");
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

            while ($this->seek() !== null && $this->seekIgnoringWhitespace() !== '>' && $this->seekIgnoringWhitespace() !== '/') {
                if ($this->seekIgnoringWhitespace(2) === '<?') {
                    $tokens[] = $this->lexPhp();
                    continue;
                }

                $attributeLine = $this->line;
                $attributeName = $this->consumeWhile(self::WHITESPACE);

                $attributeName .= $this->consumeUntil(self::WHITESPACE . '=/>');

                $hasValue = $this->seek() === '=';

                if ($hasValue) {
                    $attributeName .= $this->consume();
                }

                $tokens[] = $this->makeToken(
                    content: $attributeName,
                    type: TokenType::ATTRIBUTE_NAME,
                    line: $attributeLine,
                );

                if ($hasValue) {
                    $quote = $this->seek() === '\''
                        ? '\''
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

            if ($this->seekIgnoringWhitespace() === '>') {
                $openTagEndLine = $this->line;

                $tokens[] = $this->makeToken(
                    content: $this->consumeIncluding('>'),
                    type: TokenType::OPEN_TAG_END,
                    line: $openTagEndLine,
                );
            } elseif ($this->seekIgnoringWhitespace() === '/') {
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

        while ($this->seek(2) !== '?>' && $this->current !== null) {
            $buffer .= $this->consume();
        }

        $buffer .= $this->consume(2);

        return $this->makeToken($buffer, TokenType::XML, $line);
    }

    private function lexPhp(): Token
    {
        $line = $this->line;
        $buffer = '';

        while ($this->seek(2) !== '?>' && $this->current !== null) {
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

        while ($this->seek(3) !== '-->' && $this->current !== null) {
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

        while ($this->seek(3) !== ']]>' && $this->current !== null) {
            $buffer .= $this->consume();
        }

        $tokens[] = $this->makeToken($buffer, TokenType::CONTENT, $contentLine);

        $characterDataCloseLine = $this->line;
        $tokens[] = $this->makeToken($this->consume(3), TokenType::CHARACTER_DATA_CLOSE, $characterDataCloseLine);

        return $tokens;
    }
}
