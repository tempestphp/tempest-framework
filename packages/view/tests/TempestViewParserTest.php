<?php

namespace Tempest\View\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\View\Parser\TempestViewLexer;
use Tempest\View\Parser\TempestViewParser;
use Tempest\View\Parser\Token;
use Tempest\View\Parser\TokenCollection;
use Tempest\View\Parser\TokenType;

final class TempestViewParserTest extends TestCase
{
    public function test_parser(): void
    {
        $tokens = new TokenCollection([
            new Token('<html', TokenType::OPEN_TAG_START),
            new Token('>', TokenType::OPEN_TAG_END),
            new Token('<body ', TokenType::OPEN_TAG_START),
            new Token('class=', TokenType::ATTRIBUTE_NAME),
            new Token('"hello"', TokenType::ATTRIBUTE_VALUE),
            new Token('>', TokenType::OPEN_TAG_END),
            new Token('hello', TokenType::CONTENT),
            new Token('<x-slot/>', TokenType::SELF_CLOSING_TAG),
            new Token('</body>', TokenType::CLOSING_TAG),
            new Token("<?= 'hi' ?>", TokenType::PHP),
            new Token('<!-- test -->', TokenType::COMMENT),
            new Token('</html>', TokenType::CLOSING_TAG),
        ]);

        $parsed = new TempestViewParser($tokens)->parse();

        $this->assertSame(<<<'HTML'
        <html><body class="hello">hello<x-slot/></body><?= 'hi' ?><!-- test --></html>
        HTML, $parsed->compile());
    }

    public function test_parse_self_closing_tag_with_attributes(): void
    {
        $tokens = new TokenCollection([
            new Token('<x-foo', TokenType::OPEN_TAG_START),
            new Token(' x-bar=', TokenType::ATTRIBUTE_NAME),
            new Token('"bar"', TokenType::ATTRIBUTE_VALUE),
            new Token(' x-baz=', TokenType::ATTRIBUTE_NAME),
            new Token('"baz"', TokenType::ATTRIBUTE_VALUE),
            new Token(' />', TokenType::SELF_CLOSING_TAG_END),
        ]);

        $parsed = new TempestViewParser($tokens)->parse();

        $this->assertSame(<<<'HTML'
        <x-foo x-bar="bar" x-baz="baz" />
        HTML, $parsed->compile());
    }

    public function test_self_closing_tags_with_attributes(): void
    {
        $tokens = new TempestViewLexer('<x-foo foo="bar"/><x-bar foo="bar"/>')->lex();

        $ast = new TempestViewParser($tokens)->parse();

        $this->assertSame(<<<'HTML'
        <x-foo foo="bar"/><x-bar foo="bar"/>
        HTML, $ast->compile());
    }

    public function test_doctype(): void
    {
        $tokens = new TokenCollection([
            new Token('<!doctype html>', TokenType::DOCTYPE),
            new Token('<html', TokenType::OPEN_TAG_START),
            new Token('>', TokenType::OPEN_TAG_END),
            new Token('</html>', TokenType::CLOSING_TAG),
        ]);

        $parsed = new TempestViewParser($tokens)->parse();

        $this->assertSame(<<<'HTML'
        <!doctype html><html></html>
        HTML, $parsed->compile());
    }

    public function test_void_tags(): void
    {
        $html = <<<'HTML'
        <meta name="viewport" content="width=device-width, initial-scale=1.0"><link href="/main.css" rel="stylesheet"><div></div>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $ast = new TempestViewParser($tokens)->parse();

        $this->assertCount(3, $ast);
        $this->assertSame($html, $ast->compile());
    }

    public function test_php_within_tag(): void
    {
        $tokens = new TokenCollection([
            new Token('<div', TokenType::OPEN_TAG_START),
            new Token(' <?php if (true) { ?>', TokenType::PHP),
            new Token(' class=', TokenType::ATTRIBUTE_NAME),
            new Token('"foo"', TokenType::ATTRIBUTE_VALUE),
            new Token(' <?php } ?>', TokenType::PHP),
            new Token('>', TokenType::OPEN_TAG_END),
            new Token('</div>', TokenType::CLOSING_TAG),
        ]);

        $parsed = new TempestViewParser($tokens)->parse();

        $this->assertSame('<div <?php if (true) { ?> class="foo" <?php } ?>></div>', $parsed->compile());
    }

    #[DataProvider('data')]
    public function test_parser_from_lexed_result(string $html): void
    {
        $parsed = new TempestViewParser(new TempestViewLexer($html)->lex())->parse();

        $this->assertSame($html, $parsed->compile());
    }

    public static function data(): Generator
    {
        $files = glob(__DIR__ . '/Fixtures/html/*.html');

        foreach ($files as $file) {
            yield [file_get_contents($file)];
        }
    }
}
