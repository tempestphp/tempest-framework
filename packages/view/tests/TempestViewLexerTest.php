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

        $this->assertTokens(
            expected: [
                new Token('<html', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('<body', TokenType::OPEN_TAG_START),
                new Token(' class=', TokenType::ATTRIBUTE_NAME),
                new Token('"hello"', TokenType::ATTRIBUTE_VALUE),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('hello', TokenType::CONTENT),
                new Token('<x-slot/>', TokenType::SELF_CLOSING_TAG),
                new Token('</body>', TokenType::CLOSING_TAG),
                new Token("<?= 'hi' ?>", TokenType::PHP),
                new Token('<!-- test -->', TokenType::COMMENT),
                new Token('</html>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_lex_php(): void
    {
        $code = '<?php echo "hi"; ?>';

        $tokens = new TempestViewLexer($code)->lex();

        $this->assertTokens(
            expected: [
                new Token($code, TokenType::PHP),
            ],
            actual: $tokens,
        );
    }

    public function test_lex_comment(): void
    {
        $code = '<!-- test -->';

        $tokens = new TempestViewLexer($code)->lex();

        $this->assertTokens(
            expected: [
                new Token($code, TokenType::COMMENT),
            ],
            actual: $tokens,
        );
    }

    #[TestWith(['<x-foo />'])]
    #[TestWith(['<x-foo/>'])]
    #[TestWith(['<x-foo    />'])]
    public function test_self_closing_tag_with_and_without_space(string $tag): void
    {
        $this->assertTokens(
            expected: [
                new Token($tag, TokenType::SELF_CLOSING_TAG),
            ],
            actual: new TempestViewLexer($tag)->lex(),
        );
    }

    public function test_self_closing_tag_with_attributes(): void
    {
        $tokens = new TempestViewLexer('<x-foo x-bar="bar" x-baz="baz" />')->lex();

        $this->assertTokens(
            expected: [
                new Token('<x-foo', TokenType::OPEN_TAG_START),
                new Token(' x-bar=', TokenType::ATTRIBUTE_NAME),
                new Token('"bar"', TokenType::ATTRIBUTE_VALUE),
                new Token(' x-baz=', TokenType::ATTRIBUTE_NAME),
                new Token('"baz"', TokenType::ATTRIBUTE_VALUE),
                new Token(' />', TokenType::SELF_CLOSING_TAG_END),
            ],
            actual: $tokens,
        );
    }

    public function test_boolean_attribute_with_self_closing_tag(): void
    {
        $code = '<input disabled/>';

        $tokens = new TempestViewLexer($code)->lex();

        $this->assertTokens(
            expected: [
                new Token('<input', TokenType::OPEN_TAG_START),
                new Token(' disabled', TokenType::ATTRIBUTE_NAME),
                new Token('/>', TokenType::SELF_CLOSING_TAG_END),
            ],
            actual: $tokens,
        );
    }

    public function test_boolean_attribute_with_newline(): void
    {
        $code = '<div hidden
></div>';

        $tokens = new TempestViewLexer($code)->lex();

        $this->assertTokens(
            expected: [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token(' hidden', TokenType::ATTRIBUTE_NAME),
                new Token("\n>", TokenType::OPEN_TAG_END),
                new Token('</div>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    #[TestWith(['</x-foo>'])]
    public function test_closing_tag(string $tag): void
    {
        $this->assertTokens(
            expected: [
                new Token($tag, TokenType::CLOSING_TAG),
            ],
            actual: new TempestViewLexer($tag)->lex(),
        );
    }

    public function test_multiline_attributes(): void
    {
        $html = <<<'HTML'
        <div
            class="abc"
            foo="bar"
            x-foo
            :baz="true"
        >

        </div>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token('
    class=', TokenType::ATTRIBUTE_NAME),
                new Token('"abc"', TokenType::ATTRIBUTE_VALUE),
                new Token('
    foo=', TokenType::ATTRIBUTE_NAME),
                new Token('"bar"', TokenType::ATTRIBUTE_VALUE),
                new Token('
    x-foo', TokenType::ATTRIBUTE_NAME),
                new Token('
    :baz=', TokenType::ATTRIBUTE_NAME),
                new Token('"true"', TokenType::ATTRIBUTE_VALUE),
                new Token("\n>", TokenType::OPEN_TAG_END),
                new Token('

', TokenType::WHITESPACE),
                new Token('</div>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_whitespace(): void
    {
        $html = <<<'HTML'
        <p><strong>Test</strong> <em>Test</em></p>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('<p', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('<strong', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('Test', TokenType::CONTENT),
                new Token('</strong>', TokenType::CLOSING_TAG),
                new Token(' ', TokenType::WHITESPACE),
                new Token('<em', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('Test', TokenType::CONTENT),
                new Token('</em>', TokenType::CLOSING_TAG),
                new Token('</p>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_lexer_with_falsy_values(): void
    {
        $html = <<<'HTML'
        a0a
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('a0a', TokenType::CONTENT),
            ],
            actual: $tokens,
        );
    }

    public function test_lexer_attribute_values(): void
    {
        $tokens = new TempestViewLexer('<div x-foo="<?= $foo ?>" x-bar class="bar" x-foos>')->lex();

        $this->assertTokens(
            expected: [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token(' x-foo=', TokenType::ATTRIBUTE_NAME),
                new Token('"<?= $foo ?>"', TokenType::ATTRIBUTE_VALUE),
                new Token(' x-bar', TokenType::ATTRIBUTE_NAME),
                new Token(' class=', TokenType::ATTRIBUTE_NAME),
                new Token('"bar"', TokenType::ATTRIBUTE_VALUE),
                new Token(' x-foos', TokenType::ATTRIBUTE_NAME),
                new Token('>', TokenType::OPEN_TAG_END),
            ],
            actual: $tokens,
        );
    }

    public function test_php_within_tag(): void
    {
        $html = <<<'HTML'
        <div <?php if (true) { ?> class="foo" <?php } ?>></div>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token(' <?php if (true) { ?>', TokenType::PHP),
                new Token(' class=', TokenType::ATTRIBUTE_NAME),
                new Token('"foo"', TokenType::ATTRIBUTE_VALUE),
                new Token(' <?php } ?>', TokenType::PHP),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('</div>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_doctype(): void
    {
        $html = <<<'HTML'
        <!DOCTYPE html><html></html>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('<!DOCTYPE html>', TokenType::DOCTYPE),
                new Token('<html', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('</html>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );

        $html = <<<'HTML'
        <!doctype html><html></html>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('<!doctype html>', TokenType::DOCTYPE),
                new Token('<html', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('</html>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_attribute_with_new_line(): void
    {
        $tokens = new TempestViewLexer('<div x-foo="bar"
></div>')->lex();

        $this->assertTokens(
            expected: [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token(' x-foo=', TokenType::ATTRIBUTE_NAME),
                new Token('"bar"', TokenType::ATTRIBUTE_VALUE),
                new Token("\n>", TokenType::OPEN_TAG_END),
                new Token('</div>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_unclosed_php_tag(): void
    {
        $tokens = new TempestViewLexer('<?php echo "hi";')->lex();

        $this->assertTokens(
            expected: [
                new Token('<?php echo "hi";', TokenType::PHP),
            ],
            actual: $tokens,
        );
    }

    public function test_unclosed_comment_tag(): void
    {
        $tokens = new TempestViewLexer('<!-- comment')->lex();

        $this->assertTokens(
            expected: [
                new Token('<!-- comment', TokenType::COMMENT),
            ],
            actual: $tokens,
        );
    }

    public function test_cdata(): void
    {
        $tokens = new TempestViewLexer(<<<'RSS'
        <title><![CDATA[ {{ $post['title'] }} ]]></title>
        RSS)->lex();

        $this->assertTokens(
            expected: [
                new Token('<title', TokenType::OPEN_TAG_START),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('<![CDATA[', TokenType::CHARACTER_DATA_OPEN),
                new Token(' {{ $post[\'title\'] }} ', TokenType::CONTENT),
                new Token(']]>', TokenType::CHARACTER_DATA_CLOSE),
                new Token('</title>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    public function test_xml(): void
    {
        $tokens = new TempestViewLexer(<<<'XML'
        <?xml version="1.0" encoding="UTF-8" ?>
        XML)->lex();

        $this->assertTokens(
            expected: [
                new Token('<?xml version="1.0" encoding="UTF-8" ?>', TokenType::XML),
            ],
            actual: $tokens,
        );
    }

    public function test_single_quote_attributes(): void
    {
        $html = <<<HTML
        <div class='hello'></div>
        HTML;

        $tokens = new TempestViewLexer($html)->lex();

        $this->assertTokens(
            expected: [
                new Token('<div', TokenType::OPEN_TAG_START),
                new Token(' class=', TokenType::ATTRIBUTE_NAME),
                new Token('\'hello\'', TokenType::ATTRIBUTE_VALUE),
                new Token('>', TokenType::OPEN_TAG_END),
                new Token('</div>', TokenType::CLOSING_TAG),
            ],
            actual: $tokens,
        );
    }

    private function assertTokens(array $expected, TokenCollection $actual): void
    {
        $this->assertCount(count($expected), $actual);

        foreach ($actual as $i => $token) {
            $this->assertStringEqualsStringIgnoringLineEndings($token->content, $expected[$i]->content);
            $this->assertSame($token->type, $expected[$i]->type);
        }
    }
}
