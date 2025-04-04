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
            new Token('<?= \'hi\' ?>', TokenType::PHP),
            new Token('<!-- test -->', TokenType::COMMENT),
            new Token('</html>', TokenType::CLOSING_TAG)
        ]);

        $parsed = new TempestViewParser($tokens)->parse();

        $this->assertSame(<<<'HTML'
        <html><body class="hello">hello<x-slot/></body><?= 'hi' ?><!-- test --></html>
        HTML, $parsed->compile());
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