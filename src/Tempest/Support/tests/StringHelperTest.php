<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use function Tempest\Support\str;

/**
 * @internal
 */
final class StringHelperTest extends TestCase
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
        // TODO: support when `mb_ucfirst` has landed in PHP 8.4
        // $thisTrueaertSame('ÖffentlicheÜberraschungen', str('öffentliche-überraschungen')->pascal()->$this->equals());
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

    public function test_str_after(): void
    {
        $this->assertTrue(str('hannah')->after('han')->equals('nah'));
        $this->assertTrue(str('hannah')->after('n')->equals('nah'));
        $this->assertTrue(str('ééé hannah')->after('han')->equals('nah'));
        $this->assertTrue(str('hannah')->after('xxxx')->equals('hannah'));
        $this->assertTrue(str('hannah')->after('')->equals('hannah'));
        $this->assertTrue(str('han0nah')->after('0')->equals('nah'));
        $this->assertTrue(str('han0nah')->after(0)->equals('nah'));
        $this->assertTrue(str('han2nah')->after(2)->equals('nah'));
    }

    public function test_str_after_last(): void
    {
        $this->assertTrue(str('yvette')->afterLast('yve')->equals('tte'));
        $this->assertTrue(str('yvette')->afterLast('t')->equals('e'));
        $this->assertTrue(str('ééé yvette')->afterLast('t')->equals('e'));
        $this->assertTrue(str('yvette')->afterLast('tte')->equals(''));
        $this->assertTrue(str('yvette')->afterLast('xxxx')->equals('yvette'));
        $this->assertTrue(str('yvette')->afterLast('')->equals('yvette'));
        $this->assertTrue(str('yv0et0te')->afterLast('0')->equals('te'));
        $this->assertTrue(str('yv0et0te')->afterLast(0)->equals('te'));
        $this->assertTrue(str('yv2et2te')->afterLast(2)->equals('te'));
        $this->assertTrue(str('----foo')->afterLast('---')->equals('foo'));
    }

    public function test_str_between(): void
    {
        $this->assertTrue(str('abc')->between('', 'c')->equals('abc'));
        $this->assertTrue(str('abc')->between('a', '')->equals('abc'));
        $this->assertTrue(str('abc')->between('', '')->equals('abc'));
        $this->assertTrue(str('abc')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('dddabc')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('abcddd')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('dddabcddd')->between('a', 'c')->equals('b'));
        $this->assertTrue(str('hannah')->between('ha', 'ah')->equals('nn'));
        $this->assertTrue(str('[a]ab[b]')->between('[', ']')->equals('a]ab[b'));
        $this->assertTrue(str('foofoobar')->between('foo', 'bar')->equals('foo'));
        $this->assertTrue(str('foobarbar')->between('foo', 'bar')->equals('bar'));
        $this->assertTrue(str('12345')->between(1, 5)->equals('234'));
        $this->assertTrue(str('123456789')->between('123', '6789')->equals('45'));
        $this->assertTrue(str('nothing')->between('foo', 'bar')->equals('nothing'));
    }

    public function test_str_before(): void
    {
        $this->assertTrue(str('hannah')->before('nah')->equals('han'));
        $this->assertTrue(str('hannah')->before('n')->equals('ha'));
        $this->assertTrue(str('ééé hannah')->before('han')->equals('ééé '));
        $this->assertTrue(str('hannah')->before('xxxx')->equals('hannah'));
        $this->assertTrue(str('hannah')->before('')->equals('hannah'));
        $this->assertTrue(str('han0nah')->before('0')->equals('han'));
        $this->assertTrue(str('han0nah')->before(0)->equals('han'));
        $this->assertTrue(str('han2nah')->before(2)->equals('han'));
        $this->assertTrue(str('')->before('')->equals(''));
        $this->assertTrue(str('')->before('a')->equals(''));
        $this->assertTrue(str('a')->before('a')->equals(''));
        $this->assertTrue(str('foo@bar.com')->before('@')->equals('foo'));
        $this->assertTrue(str('foo@@bar.com')->before('@')->equals('foo'));
        $this->assertTrue(str('@foo@bar.com')->before('@')->equals(''));
    }

    public function test_str_before_last(): void
    {
        $this->assertTrue(str('yvette')->beforeLast('tte')->equals('yve'));
        $this->assertTrue(str('yvette')->beforeLast('t')->equals('yvet'));
        $this->assertTrue(str('ééé yvette')->beforeLast('yve')->equals('ééé '));
        $this->assertTrue(str('yvette')->beforeLast('yve')->equals(''));
        $this->assertTrue(str('yvette')->beforeLast('xxxx')->equals('yvette'));
        $this->assertTrue(str('yvette')->beforeLast('')->equals('yvette'));
        $this->assertTrue(str('yv0et0te')->beforeLast('0')->equals('yv0et'));
        $this->assertTrue(str('yv0et0te')->beforeLast(0)->equals('yv0et'));
        $this->assertTrue(str('yv2et2te')->beforeLast(2)->equals('yv2et'));
        $this->assertTrue(str('')->beforeLast('test')->equals(''));
        $this->assertTrue(str('yvette')->beforeLast('yvette')->equals(''));
        $this->assertTrue(str('tempest framework')->beforeLast(' ')->equals('tempest'));
        $this->assertTrue(str("yvette\tyv0et0te")->beforeLast("\t")->equals('yvette'));
    }

    public function test_starts_with(): void
    {
        $this->assertTrue(str('abc')->startsWith('a'));
        $this->assertFalse(str('abc')->startsWith('c'));
    }

    public function test_ends_with(): void
    {
        $this->assertTrue(str('abc')->endsWith('c'));
        $this->assertFalse(str('abc')->endsWith('a'));
    }
}
