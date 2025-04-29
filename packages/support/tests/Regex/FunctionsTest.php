<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Regex;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Regex\InvalidPatternException;

use function Tempest\Support\Regex\get_all_matches;
use function Tempest\Support\Regex\get_match;
use function Tempest\Support\Regex\get_matches;
use function Tempest\Support\Regex\matches;
use function Tempest\Support\Regex\replace;
use function Tempest\Support\Regex\replace_every;

/**
 * @internal
 */
final class FunctionsTest extends TestCase
{
    #[TestWith([true, 'PHP is the web scripting language of choice.', '/php/i'])]
    #[TestWith([true, 'PHP is the web scripting language of choice.', '/\bweb\b/i'])]
    #[TestWith([true, 'PHP is the web scripting language of choice.', '/PHP/'])]
    #[TestWith([true, 'PHP is the web scripting language of choice.', '/\bweb\b/'])]
    #[TestWith([true, 'http://www.php.net/index.html', '@^(?:http://)?([^/]+)@i'])]
    #[TestWith([true, 'www.php.net', '/[^.]+\.[^.]+$/'])]
    #[TestWith([false, 'PHP is the web scripting language of choice.', '/php/'])]
    #[TestWith([false, 'PHP is the website scripting language of choice.', '/\bweb\b/i'])]
    #[TestWith([false, 'php is the web scripting language of choice.', '/PHP/'])]
    #[TestWith([false, 'hello', '/[^.]+\.[^.]+$/'])]
    public function test_matches(bool $expected, string $subject, string $pattern, int $offset = 0): void
    {
        $this->assertSame($expected, matches($subject, $pattern, $offset));
    }

    public function test_matches_with_invalid_pattern(): void
    {
        $this->expectException(InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        matches('hello', '/hello');
    }

    #[TestWith(['adc', 'abc', '/b/i', 'd'])]
    #[TestWith(['April1,2003', 'April 15, 2003', '/(\w+) (\d+), (\d+)/i', '${1}1,$3'])]
    #[TestWith(['Hello, World!', 'Hello, World!', '/foo/', 'bar'])]
    public function test_replace(string $expected, string $subject, string $pattern, string $replacement): void
    {
        $this->assertSame($expected, replace($subject, $pattern, $replacement));
    }

    public function test_replace_with_callback(): void
    {
        $this->assertSame('Hello, Jon!', replace('Hello, World!', '/World/', fn () => 'Jon'));
        $this->assertSame('Count: 2', replace('Count: 1', '/\d/', fn (array $matches) => $matches[0] + 1));
    }

    public function test_replace_with_invalid_pattern(): void
    {
        $this->expectException(InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        replace('April 15, 2003', '/(\w+) (\d+), (\d+)', '${1}1,$3');
    }

    #[TestWith(['April1,2003', 'April 15, 2003', ['/(\w+) (\d+), (\d+)/i' => '${1}1,$3']])]
    #[TestWith(['The slow black bear jumps over the lazy dog.', 'The quick brown fox jumps over the lazy dog.', ['/quick/' => 'slow', '/brown/' => 'black', '/fox/' => 'bear']])]
    #[TestWith(['Hello, World!', 'Hello, World!', ['/foo/' => 'bar']])]
    public function test_replace_every(string $expected, string $subject, array $replacements): void
    {
        $this->assertSame($expected, replace_every($subject, $replacements));
    }

    public function test_replace_every_with_invalid_pattern(): void
    {
        $this->expectException(InvalidPatternException::class);
        $this->expectExceptionMessage("No ending delimiter '/' found");

        replace_every('April 15, 2003', ['/(\w+) (\d+), (\d+)' => '${1}1,$3']);
    }

    public function test_get_match(): void
    {
        $this->assertSame('10', get_match('10-abc', '/(?<id>\d+)-.*/', match: 'id'));
        $this->assertSame('10', get_match('10-abc', '/(\d+)-.*/', match: 1));
        $this->assertSame('10', get_match('10-abc', '/(\d+)-.*/'));
        $this->assertSame('10-abc', get_match('10-abc', '/\d+-.*/', match: 0));
        $this->assertSame(null, get_match('10-abc', '/\d+-.*/', match: 1));

        $this->assertSame(
            [
                'match' => "<href='https://tempestphp.com'>Tempest</href>",
                'quote' => "'",
                'href' => 'https://tempestphp.com',
            ],
            get_match("<href='https://tempestphp.com'>Tempest</href>", '/(?<match>\<href=(?<quote>[\"\'])(?<href>.+)\k<quote>\>(?:(?!\<href).)*?\<\/href\>)/g', match: [
                'match',
                'quote',
                'href',
            ]),
        );
    }

    public function test_all_matches(): void
    {
        $this->assertSame(
            [
                ['Hello'],
                ['Hello'],
            ],
            get_all_matches('Hello world, Hello universe', '/Hello/'),
        );

        $this->assertSame(
            [
                [
                    'match' => "<href='https://bsky.app'>Bluesky</href>",
                    'quote' => "'",
                    'href' => 'https://bsky.app',
                ],
                [
                    'match' => "<href='https://x.com.com'>X</href>",
                    'quote' => "'",
                    'href' => 'https://x.com.com',
                ],
            ],
            get_all_matches(
                "<href='https://bsky.app'>Bluesky</href><href='https://x.com.com'>X</href>",
                '/(?<match>\<href=(?<quote>[\"\'])(?<href>.+?)\k<quote>\>(?:(?!\<href).)*?\<\/href\>)/g',
                matches: [
                    'match',
                    'quote',
                    'href',
                ],
            ),
        );
    }

    public function test_get_matches(): void
    {
        $this->assertSame([], get_matches('The quick brown fox, then the lazy dog', '/cat/', global: true));

        $this->assertSame(
            [
                0 => '10-',
                'id' => '10-',
                1 => '10-',
            ],
            get_matches('10-abc', '/(?<id>\d+-)/'),
        );

        $this->assertSame(
            [
                [['foobar', 0]],
                [['foo', 0]],
                [['bar', 3]],
            ],
            get_matches('foobarbaz', '/(foo)(bar)/', global: true, flags: PREG_OFFSET_CAPTURE),
        );

        $this->assertSame([], get_matches('abcdef', '/^def/', global: true, offset: 3));

        $this->assertSame(
            [
                [
                    'quick brown ',
                    'lazy dog eats',
                ],
                'adjective' => [
                    'quick',
                    'lazy',
                ],
                [
                    'quick',
                    'lazy',
                ],
                'noun' => [
                    'brown',
                    'dog',
                ],
                [
                    'brown',
                    'dog',
                ],
                'action' => [
                    '',
                    'eats',
                ],
                [
                    '',
                    'eats',
                ],
            ],
            get_matches('The quick brown fox, then the lazy dog eats', '/(?<adjective>quick|lazy) (?<noun>brown|dog) (?<action>jumps|eats)?/', global: true),
        );

        $this->assertSame(
            [
                [
                    'quick brown',
                    'lazy dog',
                ],
                'adjective' => [
                    'quick',
                    'lazy',
                ],
                1 => [
                    'quick',
                    'lazy',
                ],
                'noun' => [
                    'brown',
                    'dog',
                ],
                2 => [
                    'brown',
                    'dog',
                ],
            ],
            get_matches('The quick brown fox, then the lazy dog', '/(?<adjective>quick|lazy) (?<noun>brown|dog)/', global: true),
        );
    }
}
