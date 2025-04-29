<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Str;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Str\ImmutableString;

use function Tempest\Support\arr;
use function Tempest\Support\str;

/**
 * @internal
 */
final class ManipulatesStringTest extends TestCase
{
    public function test_title(): void
    {
        $this->assertTrue(str('jefferson costella')->title()->equals('Jefferson Costella'));
        $this->assertTrue(str('jefFErson coSTella')->title()->equals('Jefferson Costella'));

        $this->assertTrue(str()->title()->equals(''));
        $this->assertTrue(str('123 tempest')->title()->equals('123 Tempest'));
        $this->assertTrue(str('❤tempest')->title()->equals('❤Tempest'));
        $this->assertTrue(str('tempest ❤')->title()->equals('Tempest ❤'));
        $this->assertTrue(str('tempest123')->title()->equals('Tempest123'));
        $this->assertTrue(str('Tempest123')->title()->equals('Tempest123'));

        $longString = 'lorem ipsum ' . str_repeat('dolor sit amet ', 1000);
        $expectedResult = 'Lorem Ipsum Dolor Sit Amet ' . str_repeat('Dolor Sit Amet ', 999);

        $this->assertTrue(str($longString)->title()->equals($expectedResult));
    }

    public function test_deduplicate(): void
    {
        $this->assertTrue(str('/some//odd//path/')->deduplicate('/')->equals('/some/odd/path/'));
        $this->assertTrue(str(' tempest   php  framework ')->deduplicate()->equals(' tempest php framework '));
        $this->assertTrue(str('whaaat')->deduplicate('a')->equals('what'));
        $this->assertTrue(str('ムだだム')->deduplicate('だ')->equals('ムだム'));
    }

    public function test_pascal(): void
    {
        $this->assertTrue(str()->pascal()->equals(''));
        $this->assertTrue(str('foo bar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('foo - bar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('foo__bar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('_foo__bar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('-foo__bar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('fooBar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('foo_bar')->pascal()->equals('FooBar'));
        $this->assertTrue(str('foo_bar1')->pascal()->equals('FooBar1'));
        $this->assertTrue(str('1foo_bar')->pascal()->equals('1fooBar'));
        $this->assertTrue(str('1foo_bar11')->pascal()->equals('1fooBar11'));
        $this->assertTrue(str('1foo_1bar1')->pascal()->equals('1foo1bar1'));
        $this->assertTrue(str('foo-barBaz')->pascal()->equals('FooBarBaz'));
        $this->assertTrue(str('foo-bar_baz')->pascal()->equals('FooBarBaz'));
        $this->assertSame('ÖffentlicheÜberraschungen', str('öffentliche-überraschungen')->pascal()->toString());
    }

    public function test_kebab(): void
    {
        $this->assertTrue(str()->kebab()->equals(''));
        $this->assertTrue(str('foo bar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('foo - bar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('foo__bar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('_foo__bar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('-foo__bar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('fooBar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('foo_bar')->kebab()->equals('foo-bar'));
        $this->assertTrue(str('foo_bar1')->kebab()->equals('foo-bar1'));
        $this->assertTrue(str('1foo_bar')->kebab()->equals('1foo-bar'));
        $this->assertTrue(str('1foo_bar11')->kebab()->equals('1foo-bar11'));
        $this->assertTrue(str('1foo_1bar1')->kebab()->equals('1foo-1bar1'));
        $this->assertTrue(str('foo-barBaz')->kebab()->equals('foo-bar-baz'));
        $this->assertTrue(str('foo-bar_baz')->kebab()->equals('foo-bar-baz'));
    }

    public function test_snake(): void
    {
        $this->assertTrue(str('')->snake()->equals(''));
        $this->assertTrue(str('foo bar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('foo - bar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('foo__bar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('_foo__bar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('-foo__bar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('fooBar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('foo_bar')->snake()->equals('foo_bar'));
        $this->assertTrue(str('foo_bar1')->snake()->equals('foo_bar1'));
        $this->assertTrue(str('1foo_bar')->snake()->equals('1foo_bar'));
        $this->assertTrue(str('1foo_bar11')->snake()->equals('1foo_bar11'));
        $this->assertTrue(str('1foo_1bar1')->snake()->equals('1foo_1bar1'));
        $this->assertTrue(str('foo-barBaz')->snake()->equals('foo_bar_baz'));
        $this->assertTrue(str('foo-bar_baz')->snake()->equals('foo_bar_baz'));
    }

    #[TestWith([0])]
    #[TestWith([16])]
    #[TestWith([100])]
    public function test_random(int $length): void
    {
        $this->assertEquals($length, str()->random($length)->length());
    }

    public function test_finish(): void
    {
        $this->assertTrue(str('foo')->finish('/')->equals('foo/'));
        $this->assertTrue(str('foo/')->finish('/')->equals('foo/'));
        $this->assertTrue(str('abbcbc')->finish('bc')->equals('abbc'));
        $this->assertTrue(str('abcbbcbc')->finish('bc')->equals('abcbbc'));
    }

    public function test_format(): void
    {
        $this->assertTrue(str('%sfoo%s')->format('[', ']')->equals('[foo]'));
    }

    public function test_str_after_first(): void
    {
        $this->assertTrue(str('hannah')->afterFirst('han')->equals('nah'));
        $this->assertTrue(str('hannah')->afterFirst(str('han'))->equals('nah'));
        $this->assertTrue(str('hannah')->afterFirst('n')->equals('nah'));
        $this->assertTrue(str('ééé hannah')->afterFirst('han')->equals('nah'));
        $this->assertTrue(str('hannah')->afterFirst('xxxx')->equals('hannah'));
        $this->assertTrue(str('hannah')->afterFirst('')->equals('hannah'));
        $this->assertTrue(str('han0nah')->afterFirst('0')->equals('nah'));
        $this->assertTrue(str('han2nah')->afterFirst('2')->equals('nah'));
        $this->assertTrue(str('@foo@bar.com')->afterFirst(['@', '.'])->equals('foo@bar.com'));
        $this->assertTrue(str('foo@bar.com')->afterFirst(['@', '.'])->equals('bar.com'));
        $this->assertTrue(str('foobar.com')->afterFirst(['@', '.'])->equals('com'));
    }

    public function test_str_after_last(): void
    {
        $this->assertTrue(str('yvette')->afterLast('yve')->equals('tte'));
        $this->assertTrue(str('yvette')->afterLast(str('yve'))->equals('tte'));
        $this->assertTrue(str('yvette')->afterLast('t')->equals('e'));
        $this->assertTrue(str('ééé yvette')->afterLast('t')->equals('e'));
        $this->assertTrue(str('yvette')->afterLast('tte')->equals(''));
        $this->assertTrue(str('yvette')->afterLast('xxxx')->equals('yvette'));
        $this->assertTrue(str('yvette')->afterLast('')->equals('yvette'));
        $this->assertTrue(str('yv0et0te')->afterLast('0')->equals('te'));
        $this->assertTrue(str('yv2et2te')->afterLast('2')->equals('te'));
        $this->assertTrue(str('----foo')->afterLast('---')->equals('foo'));
        $this->assertTrue(str('@foo@bar.com')->afterLast(['@', '.'])->equals('com'));
    }

    public function test_str_between(): void
    {
        $this->assertTrue(str('abc')->between('', 'c')->equals('abc'));
        $this->assertTrue(str('abc')->between('a', '')->equals('abc'));
        $this->assertTrue(str('abc')->between('', '')->equals('abc'));
        $this->assertTrue(str('abc')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('abc')->between(str('a'), str('c'))->equals('b'));
        $this->assertTrue(str('dddabc')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('abcddd')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('dddabcddd')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('hannah')->between('ha', 'ah')->equals('nn'));
        $this->assertTrue(str('[a]ab[b]')->between('[', ']')->equals('a]ab[b'));
        $this->assertTrue(str('foofoobar')->between('foo', 'bar')->equals('foo'));
        $this->assertTrue(str('foobarbar')->between('foo', 'bar')->equals('bar'));
        $this->assertTrue(str('12345')->between('1', '5')->equals('234'));
        $this->assertTrue(str('123456789')->between('123', '6789')->equals('45'));
        $this->assertTrue(str('nothing')->between('foo', 'bar')->equals('nothing'));
    }

    public function test_str_before(): void
    {
        $this->assertTrue(str('hannah')->before('nah')->equals('han'));
        $this->assertTrue(str('hannah')->before(str('nah'))->equals('han'));
        $this->assertTrue(str('hannah')->before('n')->equals('ha'));
        $this->assertTrue(str('ééé hannah')->before('han')->equals('ééé '));
        $this->assertTrue(str('hannah')->before('xxxx')->equals('hannah'));
        $this->assertTrue(str('hannah')->before('')->equals('hannah'));
        $this->assertTrue(str('han0nah')->before('0')->equals('han'));
        $this->assertTrue(str('han2nah')->before('2')->equals('han'));
        $this->assertTrue(str('')->before('')->equals(''));
        $this->assertTrue(str('')->before('a')->equals(''));
        $this->assertTrue(str('a')->before('a')->equals(''));
        $this->assertTrue(str('foo@bar.com')->before('@')->equals('foo'));
        $this->assertTrue(str('foo@@bar.com')->before('@')->equals('foo'));
        $this->assertTrue(str('@foo@bar.com')->before('@')->equals(''));
        $this->assertTrue(str('foo@bar.com')->before(['@', '.'])->equals('foo'));
        $this->assertTrue(str('@foo@bar.com')->before(['@', '.'])->equals(''));
    }

    public function test_str_before_last(): void
    {
        $this->assertTrue(str('yvette')->beforeLast('tte')->equals('yve'));
        $this->assertTrue(str('yvette')->beforeLast(str('tte'))->equals('yve'));
        $this->assertTrue(str('yvette')->beforeLast('t')->equals('yvet'));
        $this->assertTrue(str('ééé yvette')->beforeLast('yve')->equals('ééé '));
        $this->assertTrue(str('yvette')->beforeLast('yve')->equals(''));
        $this->assertTrue(str('yvette')->beforeLast('xxxx')->equals('yvette'));
        $this->assertTrue(str('yvette')->beforeLast('')->equals('yvette'));
        $this->assertTrue(str('yv0et0te')->beforeLast('0')->equals('yv0et'));
        $this->assertTrue(str('yv2et2te')->beforeLast('2')->equals('yv2et'));
        $this->assertTrue(str('')->beforeLast('test')->equals(''));
        $this->assertTrue(str('yvette')->beforeLast('yvette')->equals(''));
        $this->assertTrue(str('tempest framework')->beforeLast(' ')->equals('tempest'));
        $this->assertTrue(str("yvette\tyv0et0te")->beforeLast("\t")->equals('yvette'));
        $this->assertTrue(str('This is Tempest.')->beforeLast([' ', '.'])->equals('This is Tempest'));
        $this->assertTrue(str('This is Tempest')->beforeLast([' ', '.'])->equals('This is'));
    }

    public function test_starts_with(): void
    {
        $this->assertTrue(str('abc')->startsWith('a'));
        $this->assertTrue(str('abc')->startsWith(str('a')));
        $this->assertFalse(str('abc')->startsWith('c'));
    }

    public function test_ends_with(): void
    {
        $this->assertTrue(str('abc')->endsWith('c'));
        $this->assertTrue(str('abc')->endsWith(str('c')));
        $this->assertFalse(str('abc')->endsWith('a'));
    }

    public function test_replace(): void
    {
        $this->assertTrue(str('foo bar')->replace('bar', 'baz')->equals('foo baz'));
        $this->assertTrue(str('foo bar')->replace(str('bar'), 'baz')->equals('foo baz'));
        $this->assertTrue(str('foo bar')->replace('bar', str('baz'))->equals('foo baz'));
        $this->assertTrue(str('jon doe')->replace(['jon', 'jane'], 'luke')->equals('luke doe'));
        $this->assertTrue(str('jon doe')->replace(['jon', 'jane', 'doe'], ['Jon', 'Jane', 'Doe'])->equals('Jon Doe'));
        $this->assertTrue(
            str('jon doe')->replace(['jon', 'jane', 'doe'], '<censored>')->equals('<censored> <censored>'),
        );
    }

    public function test_erase(): void
    {
        $this->assertTrue(str('foo bar')->erase('bar')->equals('foo '));
        $this->assertTrue(str('foo bar')->erase('')->equals('foo bar'));
    }

    public function test_replace_last(): void
    {
        $this->assertTrue(str('foobar foobar')->replaceLast('bar', 'qux')->equals('foobar fooqux'));
        $this->assertTrue(str('foo/bar? foo/bar?')->replaceLast('bar?', 'qux?')->equals('foo/bar? foo/qux?'));
        $this->assertTrue(str('foobar foobar')->replaceLast('bar', '')->equals('foobar foo'));
        $this->assertTrue(str('foobar foobar')->replaceLast('xxx', 'yyy')->equals('foobar foobar'));
        $this->assertTrue(str('foobar foobar')->replaceLast('', 'yyy')->equals('foobar foobar'));
        $this->assertTrue(str('Malmö Jönköping')->replaceLast('ö', 'xxx')->equals('Malmö Jönkxxxping'));
        $this->assertTrue(str('Malmö Jönköping')->replaceLast('', 'yyy')->equals('Malmö Jönköping'));

        $this->assertEquals('foobar foobar', str('foobar foobar')->replaceLast([], 'baz')->toString());
        $this->assertEquals('foobar bazbar', str('foobar foobar')->replaceLast(['foo', 'bar'], 'baz')->toString());
        $this->assertEquals('foobar foobaz', str('foobar foobar')->replaceLast(['bar', 'foo'], 'baz')->toString());
    }

    public function test_replace_first(): void
    {
        $this->assertTrue(str('foobar foobar')->replaceFirst('bar', 'qux')->equals('fooqux foobar'));
        $this->assertTrue(str('foo/bar? foo/bar?')->replaceFirst('bar?', 'qux?')->equals('foo/qux? foo/bar?'));
        $this->assertTrue(str('foobar foobar')->replaceFirst('bar', '')->equals('foo foobar'));
        $this->assertTrue(str('foobar foobar')->replaceFirst('xxx', 'yyy')->equals('foobar foobar'));
        $this->assertTrue(str('foobar foobar')->replaceFirst('', 'yyy')->equals('foobar foobar'));
        $this->assertTrue(str('Jönköping Malmö')->replaceFirst('ö', 'xxx')->equals('Jxxxnköping Malmö'));
        $this->assertTrue(str('Jönköping Malmö')->replaceFirst('', 'yyy')->equals('Jönköping Malmö'));

        $this->assertTrue(str('foobar foobar')->replaceFirst([], 'baz')->equals('foobar foobar'));
        $this->assertTrue(str('foobar foobar')->replaceFirst(['foobar', 'foo'], 'baz')->equals('baz foobar'));
    }

    public function test_replace_end(): void
    {
        $this->assertTrue(str('foobar fooqux')->replaceEnd('bar', 'qux')->equals('foobar fooqux'));
        $this->assertTrue(str('foo/bar? foo/qux?')->replaceEnd('bar?', 'qux?')->equals('foo/bar? foo/qux?'));
        $this->assertTrue(str('foobar foo')->replaceEnd('bar', '')->equals('foobar foo'));
        $this->assertTrue(str('foobar foobar')->replaceEnd('xxx', 'yyy')->equals('foobar foobar'));
        $this->assertTrue(str('foobar foobar')->replaceEnd('', 'yyy')->equals('foobar foobar'));
        $this->assertTrue(str('fooxxx foobar')->replaceEnd('xxx', 'yyy')->equals('fooxxx foobar'));
        $this->assertTrue(str('Malmö Jönköping')->replaceEnd('ö', 'xxx')->equals('Malmö Jönköping'));
        $this->assertTrue(str('Malmö Jönköping')->replaceEnd('öping', 'yyy')->equals('Malmö Jönkyyy'));

        $this->assertEquals('foobar foo', str('foobar foo')->replaceEnd([], 'baz')->toString());
        $this->assertEquals('foobar baz', str('foobar foo')->replaceEnd(['bar', 'foo'], 'baz')->toString());
    }

    public function test_append(): void
    {
        $this->assertTrue(str('foo')->append('bar')->equals('foobar'));
        $this->assertTrue(str('foo')->append('bar', 'baz')->equals('foobarbaz'));
        $this->assertTrue(str('foo')->append(str('bar'), str('baz'))->equals('foobarbaz'));
    }

    public function test_prepend(): void
    {
        $this->assertTrue(str('bar')->prepend('foo')->equals('foobar'));
        $this->assertTrue(str('baz')->prepend('bar', 'foo')->equals('barfoobaz'));
        $this->assertTrue(str('baz')->prepend(str('bar'), str('foo'))->equals('barfoobaz'));
    }

    public function test_match(): void
    {
        $match = str('10-abc')->match('/(?<id>\d+-)/', match: 'id');

        $this->assertSame('10-', $match);
    }

    public function test_matches(): void
    {
        $this->assertTrue(str('10-abc')->matches('/(?<id>\d+-)/'));
        $this->assertTrue(str('10-abc')->matches('/(\d+-)/'));
        $this->assertTrue(str('10-abc')->matches('/\d+-/'));
        $this->assertFalse(str('10abc')->matches('/\d+-/'));
        $this->assertFalse(str('abc')->matches('/\d+-/'));
    }

    public function test_replace_regex(): void
    {
        $this->assertTrue(str('10-abc')->replaceRegex('/(?<id>\d+-)/', '')->equals('abc'));
        $this->assertTrue(str('10-abc')->replaceRegex('/(?<id>\d+-)/', fn () => '')->equals('abc'));
        $this->assertTrue(str('10-abc')->replaceRegex(['/\d/', '/\w/'], ['#', 'X'])->equals('##-XXX'));
    }

    public function test_match_all(): void
    {
        $this->assertSame([
            ['Hello'],
            ['Hello'],
        ], str('Hello world, Hello universe')->matchAll('/Hello/')->toArray());

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
            str("<href='https://bsky.app'>Bluesky</href><href='https://x.com.com'>X</href>")
                ->matchAll('/(?<match>\<href=(?<quote>[\"\'])(?<href>.+?)\k<quote>\>(?:(?!\<href).)*?\<\/href\>)/g', matches: [
                    'match',
                    'quote',
                    'href',
                ])
                ->toArray(),
        );
    }

    public function test_explode(): void
    {
        $this->assertTrue(str('path/to/tempest')->explode('/')->equals(['path', 'to', 'tempest']));
        $this->assertTrue(str('john doe')->explode()->equals(['john', 'doe']));
    }

    public function test_implode(): void
    {
        $this->assertSame('path/to/tempest', ImmutableString::implode(['path', 'to', 'tempest'], '/')->toString());
        $this->assertSame('john doe', ImmutableString::implode(['john', 'doe'])->toString());
        $this->assertSame('path/to/tempest', ImmutableString::implode(arr(['path', 'to', 'tempest']), '/')->toString());
        $this->assertSame('john doe', ImmutableString::implode(arr(['john', 'doe']))->toString());
    }

    #[TestWith([['Jon', 'Jane'], 'Jon and Jane'])]
    #[TestWith([['Jon', 'Jane', 'Jill'], 'Jon, Jane and Jill'])]
    public function test_join(array $initial, string $expected): void
    {
        $this->assertEquals($expected, ImmutableString::join($initial));
    }

    #[TestWith([['Jon', 'Jane'], ', ', ' and maybe ', 'Jon and maybe Jane'])]
    #[TestWith([['Jon', 'Jane', 'Jill'], ' + ', ' and ', 'Jon + Jane and Jill'])]
    #[TestWith([['Jon', 'Jane', 'Jill'], ' + ', null, 'Jon + Jane + Jill'])]
    public function test_join_with_glues(array $initial, string $glue, ?string $finalGlue, string $expected): void
    {
        $this->assertTrue(ImmutableString::join($initial, $glue, $finalGlue)->equals($expected));
    }

    public function test_excerpt(): void
    {
        $content = str('a
b
c
d
e
f
g');

        $this->assertTrue($content->excerpt(2, 4)->equals('b
c
d'));

        $this->assertTrue($content->excerpt(-10, 2)->equals('a
b'));

        $this->assertTrue($content->excerpt(7, 100)->equals('g'));

        $this->assertSame([2 => 'b', 3 => 'c', 4 => 'd'], $content->excerpt(2, 4, asArray: true)->toArray());
    }

    public function test_wrap(): void
    {
        $this->assertSame('Leon Scott Kennedy', str('Scott')->wrap(before: 'Leon ', after: ' Kennedy')->toString());
        $this->assertSame('"value"', str('value')->wrap('"')->toString());
    }

    public function test_unwrap(): void
    {
        $this->assertSame('Scott', str('Leon Scott Kennedy')->unwrap(before: 'Leon ', after: ' Kennedy')->toString());
        $this->assertSame('value', str('"value"')->unwrap('"')->toString());
        $this->assertSame('"value"', str('"value"')->unwrap('`')->toString());
        $this->assertSame('[value', str('[value')->unwrap('[', ']')->toString());
        $this->assertEquals('some: "json"', str('{some: "json"}')->unwrap('{', '}')->toString());

        $this->assertSame('value', str('[value')->unwrap('[', ']', strict: false)->toString());
        $this->assertSame('value', str('value]')->unwrap('[', ']', strict: false)->toString());
        $this->assertSame('Scott', str('Scott Kennedy')->unwrap(before: 'Leon ', after: ' Kennedy', strict: false)->toString());
    }

    public function test_start(): void
    {
        $this->assertSame('Leon Scott Kennedy', str('Scott Kennedy')->start('Leon ')->toString());
        $this->assertSame('Leon Scott Kennedy', str('Leon Scott Kennedy')->start('Leon ')->toString());
    }

    public function test_limit(): void
    {
        $this->assertSame('Lorem', str('Lorem ipsum')->truncate(5)->toString());
        $this->assertSame('Lorem...', str('Lorem ipsum')->truncate(5, end: '...')->toString());
        $this->assertSame('...', str('Lorem ipsum')->truncate(0, end: '...')->toString());
        $this->assertSame('L...', str('Lorem ipsum')->truncate(1, end: '...')->toString());
        $this->assertSame('Lorem ipsum', str('Lorem ipsum')->truncate(100)->toString());
        $this->assertSame('Lorem ipsum', str('Lorem ipsum')->truncate(100, end: '...')->toString());
    }

    public function test_substr(): void
    {
        $this->assertSame('Lorem', str('Lorem ipsum')->substr(0, length: 5)->toString());
        $this->assertSame('ipsum', str('Lorem ipsum')->substr(6, length: 5)->toString());
        $this->assertSame('ipsum', str('Lorem ipsum')->substr(6)->toString());
        $this->assertSame('ipsum', str('Lorem ipsum')->substr(-5)->toString());
        $this->assertSame('ipsum', str('Lorem ipsum')->substr(-5, length: 5)->toString());
    }

    public function test_take(): void
    {
        // positive
        $this->assertSame('Lorem', str('Lorem ipsum')->take(5)->toString());
        $this->assertSame('Lorem ipsum', str('Lorem ipsum')->take(100)->toString());

        // negative
        $this->assertSame('ipsum', str('Lorem ipsum')->take(-5)->toString());
    }

    public function test_chunk(): void
    {
        $this->assertTrue(str(PHP_EOL)->chunk(100)->equals([PHP_EOL]));
        $this->assertTrue(str('')->chunk(1)->equals(['']));
        $this->assertTrue(str('123')->chunk(-1)->equals([]));
        $this->assertTrue(str('123')->chunk(1)->equals(['1', '2', '3']));
        $this->assertTrue(str('123')->chunk(1000)->equals(['123']));
        $this->assertTrue(str('foobarbaz')->chunk(3)->equals(['foo', 'bar', 'baz']));
        $this->assertTrue(str('foobarbaz22')->chunk(3)->equals(['foo', 'bar', 'baz', '22']));
    }

    public function test_insert_at(): void
    {
        $this->assertSame('foo', str()->insertAt(0, 'foo')->toString());
        $this->assertSame('foo', str()->insertAt(-1, 'foo')->toString());
        $this->assertSame('foo', str()->insertAt(100, 'foo')->toString());
        $this->assertSame('foo', str()->insertAt(-100, 'foo')->toString());
        $this->assertSame('foobar', str('bar')->insertAt(0, 'foo')->toString());
        $this->assertSame('barfoo', str('bar')->insertAt(3, 'foo')->toString());
        $this->assertSame('foobarbaz', str('foobaz')->insertAt(3, 'bar')->toString());
        $this->assertSame('123', str('13')->insertAt(-1, '2')->toString());
    }

    public function test_replace_at(): void
    {
        $this->assertSame('foobar', str('foo2bar')->replaceAt(4, -1, '')->toString());
        $this->assertSame('foobar', str('foo2bar')->replaceAt(3, 1, '')->toString());
        $this->assertSame('fooquxbar', str('foo2bar')->replaceAt(3, 1, 'qux')->toString());
        $this->assertSame('foobarbaz', str('barbaz')->replaceAt(0, 0, 'foo')->toString());
        $this->assertSame('barbazfoo', str('barbaz')->replaceAt(6, 0, 'foo')->toString());
        $this->assertSame('bar', str('foo')->replaceAt(0, 3, 'bar')->toString());
        $this->assertSame('abc1', str('abcd')->replaceAt(-1, 1, '1')->toString());
        $this->assertSame('ab1d', str('abcd')->replaceAt(-1, -1, '1')->toString());
        $this->assertSame('abc', str('abc')->replaceAt(3, 1, '')->toString());
    }

    #[TestWith(['Hello, you!', 'Hello, you!', ['You' => 'World']])]
    #[TestWith(['Hello, World!', 'Hello, You!', ['You' => 'World']])]
    #[TestWith(['Foo', 'Foo', ['bar' => 'baz']])]
    public function test_replace_every(string $expected, string $haystack, iterable $replacements): void
    {
        $this->assertSame($expected, str($haystack)->replaceEvery($replacements)->toString());
    }

    public function test_strip_tags(): void
    {
        $this->assertSame('Hello World', str('<p>Hello World</p>')->stripTags()->toString());
        $this->assertSame('Hello World', str('<p>Hello <strong>World</strong></p>')->stripTags()->toString());
        $this->assertSame('Hello <strong>World</strong>', str('<p>Hello <strong>World</strong></p>')->stripTags(allowed: '<strong>')->toString());
        $this->assertSame('<p>Hello World</p>', str('<p>Hello <strong>World</strong></p>')->stripTags(allowed: '<p>')->toString());

        $this->assertSame('Hello <strong>World</strong>', str('<p>Hello <strong>World</strong></p>')->stripTags(allowed: 'strong')->toString());
        $this->assertSame('<p>Hello World</p>', str('<p>Hello <strong>World</strong></p>')->stripTags(allowed: 'p')->toString());
    }

    public function test_align_center(): void
    {
        $this->assertSame('  foo  ', str('foo')->alignCenter(7)->toString());
        $this->assertSame('  foo  ', str(' foo ')->alignCenter(7)->toString());
        $this->assertSame('   foo    ', str('foo')->alignCenter(10)->toString());

        $this->assertSame('  foo  ', str('foo')->alignCenter(2, padding: 2)->toString());
        $this->assertSame('   foo    ', str('foo')->alignCenter(10, padding: 2)->toString());
        $this->assertSame('  foo  ', str(' foo ')->alignCenter(2, padding: 2)->toString());
    }

    public function test_align_right(): void
    {
        $this->assertSame('foo', str('foo')->alignRight(3)->toString());
        $this->assertSame('       foo', str('foo')->alignRight(10)->toString());
        $this->assertSame('       foo', str(' foo')->alignRight(10)->toString());
        $this->assertSame('     foo  ', str(' foo')->alignRight(10, padding: 2)->toString());
        $this->assertSame('  foo  ', str('foo')->alignRight(2, padding: 2)->toString());
    }

    public function test_align_left(): void
    {
        $this->assertSame('foo', str('foo')->alignLeft(3)->toString());
        $this->assertSame('foo       ', str('foo')->alignLeft(10)->toString());
        $this->assertSame('foo       ', str(' foo')->alignLeft(10)->toString());
        $this->assertSame('  foo     ', str(' foo')->alignLeft(10, padding: 2)->toString());
        $this->assertSame('  foo  ', str('foo')->alignLeft(2, padding: 2)->toString());
    }

    public function test_contains(): void
    {
        $this->assertTrue(str('foo')->contains('fo'));
        $this->assertFalse(str('foo')->contains('bar'));
        $this->assertTrue(str('foo')->contains(['bar', 'foo']));
        $this->assertFalse(str('foo')->contains([]));
    }

    public function test_levenshtein(): void
    {
        $this->assertSame(0, str('foo')->levenshtein('foo'));
        $this->assertSame(3, str('foo')->levenshtein('bar'));
    }

    public function test_is_empty(): void
    {
        $this->assertTrue(str('')->isEmpty());
        $this->assertFalse(str('a')->isEmpty());
    }

    public function test_is_not_empty(): void
    {
        $this->assertTrue(str('a')->isNotEmpty());
        $this->assertFalse(str('')->isNotEmpty());
    }

    public function test_reverse(): void
    {
        $this->assertSame('oof', str('foo')->reverse()->toString());
        $this->assertSame('…oof', str('foo…')->reverse()->toString());
    }

    public function test_truncate_start(): void
    {
        $this->assertSame('Lorem ipsum', str('Lorem ipsum')->truncateStart(20, start: '…')->toString());
        $this->assertSame('…ipsum', str('Lorem ipsum')->truncateStart(5, start: '…')->toString());
        $this->assertSame('…', str('Lorem ipsum')->truncateStart(0, start: '…')->toString());
        $this->assertSame('…orem ipsum', str('Lorem ipsum')->truncateStart(-1, start: '…')->toString());
    }

    public function test_tap(): void
    {
        $string = str('foo');

        $log = '';
        $result = $string->tap(function (ImmutableString $string) use (&$log): void {
            $log .= $string->toString();
        });

        $this->assertSame($string, $result);
        $this->assertEquals('foo', $log);
    }

    public function test_class_basename(): void
    {
        $this->assertSame('ImmutableString', str(ImmutableString::class)->classBasename()->toString());
        $this->assertSame('ImmutableString', str('ImmutableString')->classBasename()->toString());
        $this->assertSame('', str()->classBasename()->toString());
    }

    public function test_basename(): void
    {
        $this->assertSame('file.txt', str('path/to/file.txt')->basename()->toString());
        $this->assertSame('file.txt', str('file.txt')->basename()->toString());
        $this->assertSame('file', str('file.txt')->basename('.txt')->toString());
        $this->assertSame('', str()->basename()->toString());
    }

    public function test_ascii(): void
    {
        $this->assertSame('@', str('@')->ascii()->toString());
        $this->assertSame('u', str('ü')->ascii()->toString());
        $this->assertSame('', str('')->ascii()->toString());
        $this->assertSame('a!2e', str('a!2ë')->ascii()->toString());
    }

    public function test_slug(): void
    {
        $this->assertSame('hello-world', str('hello world')->slug()->toString());
        $this->assertSame('hello-world', str('hello-world')->slug()->toString());
        $this->assertSame('hello-world', str('hello_world')->slug()->toString());
        $this->assertSame('hello_world', str('hello_world')->slug(separator: '_')->toString());
        $this->assertSame('user-at-host', str('user@host')->slug()->toString());
        $this->assertSame('slam-dnya', str('سلام دنیا')->slug(separator: '-')->toString());
        $this->assertSame('sometext', str('some text')->slug(separator: '')->toString());
        $this->assertSame('', str()->slug(separator: '')->toString());
        $this->assertSame('bsm-allh', str('بسم الله')->slug(separator: '-', replacements: ['allh' => 'allah'])->toString());
        $this->assertSame('500-dollar-bill', str('500$ bill')->slug('-', replaceSymbols: true)->toString());
        $this->assertSame('500-bill', str('500$ bill')->slug('-', replaceSymbols: false)->toString());
        $this->assertSame('500-dollar-bill', str('500--$----bill')->slug(separator: '-')->toString());
        $this->assertSame('500-dollar-bill', str('500-$-bill')->slug(separator: '-')->toString());
        $this->assertSame('500-dollar-bill', str('500$--bill')->slug(separator: '-')->toString());
        $this->assertSame('500-dollar-bill', str('500-$--bill')->slug(separator: '-')->toString());
        $this->assertSame('ahmdfyalmdrs', str('أحمد@المدرسة')->slug(separator: '-', replacements: ['@' => 'في'])->toString());
    }

    public function test_is_ascii(): void
    {
        $this->assertTrue(str('hello')->isAscii());
        $this->assertTrue(str()->isAscii());

        $this->assertFalse(str('helloü')->isAscii());
        $this->assertFalse(str('بسم الله')->isAscii());
    }

    #[TestWith(['foo bar baz', ['foo', 'bar', 'baz']])]
    #[TestWith(['foo-bar-baz', ['foo', 'bar', 'baz']])]
    #[TestWith(['1foo_bar1', ['1foo_bar1']])]
    #[TestWith(['fooBar', ['fooBar']])]
    #[TestWith(['Jon Doe', ['Jon', 'Doe']])]
    #[TestWith(['-Jon Doe', ['Jon', 'Doe']])]
    #[TestWith(['_Jon_Doe', ['_Jon_Doe']])]
    public function test_words(string $input, array $output): void
    {
        $this->assertEquals($output, str($input)->words()->toArray());
    }

    #[TestWith(['foo bar baz', 'Foo bar baz'])]
    #[TestWith(['foo-bar-baz', 'Foo bar baz'])]
    #[TestWith(['Foo Bar', 'Foo bar'])]
    #[TestWith(['1foo_bar1', '1foo_bar1'])]
    #[TestWith(['getting-started', 'Getting started'])]
    #[TestWith(['Getting Started', 'Getting started'])]
    public function test_sentence(string $input, string $output): void
    {
        $this->assertEquals($output, str($input)->sentence()->toString());
    }

    #[TestWith(['http://tempestphp.com', 'http://', 'tempestphp.com'])]
    #[TestWith(['http://tempestphp.com', ['http://', 'https://'], 'tempestphp.com'])]
    #[TestWith(['http://tempestphp.com', '', 'http://tempestphp.com'])]
    #[TestWith(['http://tempestphp.com', [], 'http://tempestphp.com'])]
    #[TestWith(['http://tempestphp.com', '://', 'http://tempestphp.com'])]
    #[TestWith(['http://tempestphp.com', ['http', 'http://'], '://tempestphp.com'])]
    public function test_strip_start(string $input, string|array $strip, string $output): void
    {
        $this->assertEquals($output, str($input)->stripStart($strip)->toString());
    }

    #[TestWith(['foo_bar', '_bar', 'foo'])]
    #[TestWith(['foo.bar/', '/', 'foo.bar'])]
    #[TestWith(['foo.bar/', ['/', 'bar/'], 'foo.bar'])]
    #[TestWith(['foo.bar/', ['bar/', '/'], 'foo.'])]
    public function test_strip_end(string $input, string|array $strip, string $output): void
    {
        $this->assertEquals($output, str($input)->stripEnd($strip)->toString());
    }

    #[TestWith(['aaay ', 'aaay', 5])]
    #[TestWith(['aaayy', 'aaay', 5, 'y'])]
    #[TestWith(['Yeet', 'Yee', 4, 't'])]
    #[TestWith(['مرحباااا', 'مرحبا', 8, 'ا'])]
    public function test_pad_right(string $expected, string $str, int $totalLength, string $padString = ' '): void
    {
        $this->assertSame($expected, str($str)->padRight($totalLength, $padString)->toString());
    }

    #[TestWith([' aaay', 'aaay', 5])]
    #[TestWith(['Aaaay', 'aaay', 5, 'A'])]
    #[TestWith(['Yeet', 'eet', 4, 'Yeeeee'])]
    #[TestWith(['ممممرحبا', 'مرحبا', 8, 'م'])]
    public function test_pad_left(string $expected, string $str, int $totalLength, string $padString = ' '): void
    {
        $this->assertSame($expected, str($str)->padLeft($totalLength, $padString)->toString());
    }
}
