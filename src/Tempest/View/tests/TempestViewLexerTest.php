<?php

namespace Tempest\View\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\View\Parser\TempestViewLexer;
use Tempest\View\Parser\Token;
use Tempest\View\Parser\TokenCollection;
use Tempest\View\Parser\TokenType;

final class TempestViewLexerTest extends TestCase
{
    public function test_lexer(): void
    {
        $html = <<<HTML
        <html><body class="hello">hello<x-slot/></body><?= 'hi' ?><!-- test --></html>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens([
            new Token('<html', TokenType::OPEN_TAG_START),
            new Token('>', TokenType::OPEN_TAG_END),
            new Token('<body', TokenType::OPEN_TAG_START),
            new Token(' class=', TokenType::ATTRIBUTE_NAME),
            new Token('"hello"', TokenType::ATTRIBUTE_VALUE),
            new Token('>', TokenType::OPEN_TAG_END),
            new Token('hello', TokenType::CONTENT),
            new Token('<x-slot/>', TokenType::SELF_CLOSING_TAG),
            new Token('</body>', TokenType::CLOSING_TAG),
            new Token('<?= \'hi\' ?>', TokenType::PHP),
            new Token('<!-- test -->', TokenType::COMMENT),
            new Token('</html>', TokenType::CLOSING_TAG)
        ], $tokens);
    }

    #[TestWith(['<x-foo />'])]
    #[TestWith(['<x-foo/>'])]
    #[TestWith(['<x-foo    />'])]
    public function test_self_closing_tag_with_and_without_space(string $tag): void
    {
        $this->assertTokens([
            new Token($tag, TokenType::SELF_CLOSING_TAG),
        ], new TempestViewLexer($tag)->lex());
    }

    #[TestWith(['</x-foo>'])]
    public function test_self_closing_tag(string $tag): void
    {
        $this->assertTokens([
            new Token($tag, TokenType::CLOSING_TAG),
        ], new TempestViewLexer($tag)->lex());
    }

    public function test_lexer_with_falsy_values(): void
    {
        $html = <<<'HTML'
        a0a
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens([
            new Token('a0a', TokenType::CONTENT)
        ], $tokens);
    }

    public function test_lexer_attribute_values(): void
    {
        $tokens = new TempestViewLexer('<div x-foo="<?= $foo ?>" x-bar class="bar" x-foos>')->lex();

        $this->assertTokens(
            [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token(' x-foo=', TokenType::ATTRIBUTE_NAME),
                new Token('"<?= $foo ?>"', TokenType::ATTRIBUTE_VALUE),
                new Token(' x-bar', TokenType::ATTRIBUTE_NAME),
                new Token(' class=', TokenType::ATTRIBUTE_NAME),
                new Token('"bar"', TokenType::ATTRIBUTE_VALUE),
                new Token(' x-foos', TokenType::ATTRIBUTE_NAME),
                new Token('>', TokenType::OPEN_TAG_END),
            ],
            $tokens,
        );
    }

    private function assertTokens(array $expected, TokenCollection $actual): void
    {
        $this->assertCount(count($expected), $actual);

        foreach ($actual as $i => $token) {
            $this->assertSame($token->content, $expected[$i]->content);
            $this->assertSame($token->type, $expected[$i]->type);
        }
    }
}