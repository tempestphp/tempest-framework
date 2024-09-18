<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\StringHelper;

/**
 * @internal
 * @small
 */
final class StringHelperTest extends TestCase
{
    public function test_title(): void
    {
        $this->assertSame('Jefferson Costella', StringHelper::title('jefferson costella'));
        $this->assertSame('Jefferson Costella', StringHelper::title('jefFErson coSTella'));

        $this->assertSame('', StringHelper::title(''));
        $this->assertSame('123 Tempest', StringHelper::title('123 tempest'));
        $this->assertSame('❤Tempest', StringHelper::title('❤tempest'));
        $this->assertSame('Tempest ❤', StringHelper::title('tempest ❤'));
        $this->assertSame('Tempest123', StringHelper::title('tempest123'));
        $this->assertSame('Tempest123', StringHelper::title('Tempest123'));

        $longString = 'lorem ipsum '.str_repeat('dolor sit amet ', 1000);
        $expectedResult = 'Lorem Ipsum Dolor Sit Amet '.str_repeat('Dolor Sit Amet ', 999);
        $this->assertSame($expectedResult, StringHelper::title($longString));
    }

    public function test_deduplicate(): void
    {
        $this->assertSame('/some/odd/path/', StringHelper::deduplicate('/some//odd//path/', '/'));
        $this->assertSame(' tempest php framework ', StringHelper::deduplicate(' tempest   php  framework '));
        $this->assertSame('what', StringHelper::deduplicate('whaaat', 'a'));
        $this->assertSame('ムだム', StringHelper::deduplicate('ムだだム', 'だ'));
    }

    public function test_pascal(): void
    {
        $this->assertSame('', StringHelper::pascal(''));
        $this->assertSame('FooBar', StringHelper::pascal('foo bar'));
        $this->assertSame('FooBar', StringHelper::pascal('foo - bar'));
        $this->assertSame('FooBar', StringHelper::pascal('foo__bar'));
        $this->assertSame('FooBar', StringHelper::pascal('_foo__bar'));
        $this->assertSame('FooBar', StringHelper::pascal('-foo__bar'));
        $this->assertSame('FooBar', StringHelper::pascal('fooBar'));
        $this->assertSame('FooBar', StringHelper::pascal('foo_bar'));
        $this->assertSame('FooBar1', StringHelper::pascal('foo_bar1'));
        $this->assertSame('1fooBar', StringHelper::pascal('1foo_bar'));
        $this->assertSame('1fooBar11', StringHelper::pascal('1foo_bar11'));
        $this->assertSame('1foo1bar1', StringHelper::pascal('1foo_1bar1'));
        $this->assertSame('FooBarBaz', StringHelper::pascal('foo-barBaz'));
        $this->assertSame('FooBarBaz', StringHelper::pascal('foo-bar_baz'));
        // TODO: support when `mb_ucfirst` has landed in PHP 8.4
        // $this->assertSame('ÖffentlicheÜberraschungen', StringHelper::pascal('öffentliche-überraschungen'));
    }

    public function test_kebab(): void
    {
        $this->assertSame('', StringHelper::kebab(''));
        $this->assertSame('foo-bar', StringHelper::kebab('foo bar'));
        $this->assertSame('foo-bar', StringHelper::kebab('foo - bar'));
        $this->assertSame('foo-bar', StringHelper::kebab('foo__bar'));
        $this->assertSame('foo-bar', StringHelper::kebab('_foo__bar'));
        $this->assertSame('foo-bar', StringHelper::kebab('-foo__bar'));
        $this->assertSame('foo-bar', StringHelper::kebab('fooBar'));
        $this->assertSame('foo-bar', StringHelper::kebab('foo_bar'));
        $this->assertSame('foo-bar1', StringHelper::kebab('foo_bar1'));
        $this->assertSame('1foo-bar', StringHelper::kebab('1foo_bar'));
        $this->assertSame('1foo-bar11', StringHelper::kebab('1foo_bar11'));
        $this->assertSame('1foo-1bar1', StringHelper::kebab('1foo_1bar1'));
        $this->assertSame('foo-bar-baz', StringHelper::kebab('foo-barBaz'));
        $this->assertSame('foo-bar-baz', StringHelper::kebab('foo-bar_baz'));
    }

    public function test_snake(): void
    {
        $this->assertSame('', StringHelper::snake(''));
        $this->assertSame('foo_bar', StringHelper::snake('foo bar'));
        $this->assertSame('foo_bar', StringHelper::snake('foo - bar'));
        $this->assertSame('foo_bar', StringHelper::snake('foo__bar'));
        $this->assertSame('foo_bar', StringHelper::snake('_foo__bar'));
        $this->assertSame('foo_bar', StringHelper::snake('-foo__bar'));
        $this->assertSame('foo_bar', StringHelper::snake('fooBar'));
        $this->assertSame('foo_bar', StringHelper::snake('foo_bar'));
        $this->assertSame('foo_bar1', StringHelper::snake('foo_bar1'));
        $this->assertSame('1foo_bar', StringHelper::snake('1foo_bar'));
        $this->assertSame('1foo_bar11', StringHelper::snake('1foo_bar11'));
        $this->assertSame('1foo_1bar1', StringHelper::snake('1foo_1bar1'));
        $this->assertSame('foo_bar_baz', StringHelper::snake('foo-barBaz'));
        $this->assertSame('foo_bar_baz', StringHelper::snake('foo-bar_baz'));
    }

    #[TestWith([0])]
    #[TestWith([16])]
    #[TestWith([100])]
    public function test_random(int $length): void
    {
        $this->assertEquals($length, strlen(StringHelper::random($length)));
    }

    public function test_finish(): void
    {
        $this->assertSame('foo/', StringHelper::finish('foo', '/'));
        $this->assertSame('foo/', StringHelper::finish('foo/', '/'));
        $this->assertSame('abbc', StringHelper::finish('abbcbc', 'bc'));
        $this->assertSame('abcbbc', StringHelper::finish('abcbbcbc', 'bc'));
    }

    public function test_str_after(): void
    {
        $this->assertSame('nah', StringHelper::after('hannah', 'han'));
        $this->assertSame('nah', StringHelper::after('hannah', 'n'));
        $this->assertSame('nah', StringHelper::after('ééé hannah', 'han'));
        $this->assertSame('hannah', StringHelper::after('hannah', 'xxxx'));
        $this->assertSame('hannah', StringHelper::after('hannah', ''));
        $this->assertSame('nah', StringHelper::after('han0nah', '0'));
        $this->assertSame('nah', StringHelper::after('han0nah', 0));
        $this->assertSame('nah', StringHelper::after('han2nah', 2));
    }

    public function test_str_after_last(): void
    {
        $this->assertSame('tte', StringHelper::afterLast('yvette', 'yve'));
        $this->assertSame('e', StringHelper::afterLast('yvette', 't'));
        $this->assertSame('e', StringHelper::afterLast('ééé yvette', 't'));
        $this->assertSame('', StringHelper::afterLast('yvette', 'tte'));
        $this->assertSame('yvette', StringHelper::afterLast('yvette', 'xxxx'));
        $this->assertSame('yvette', StringHelper::afterLast('yvette', ''));
        $this->assertSame('te', StringHelper::afterLast('yv0et0te', '0'));
        $this->assertSame('te', StringHelper::afterLast('yv0et0te', 0));
        $this->assertSame('te', StringHelper::afterLast('yv2et2te', 2));
        $this->assertSame('foo', StringHelper::afterLast('----foo', '---'));
    }

    public function test_str_between(): void
    {
        $this->assertSame('abc', StringHelper::between('abc', '', 'c'));
        $this->assertSame('abc', StringHelper::between('abc', 'a', ''));
        $this->assertSame('abc', StringHelper::between('abc', '', ''));
        $this->assertSame('b', StringHelper::between('abc', 'a', 'c'));
        $this->assertSame('b', StringHelper::between('dddabc', 'a', 'c'));
        $this->assertSame('b', StringHelper::between('abcddd', 'a', 'c'));
        $this->assertSame('b', StringHelper::between('dddabcddd', 'a', 'c'));
        $this->assertSame('nn', StringHelper::between('hannah', 'ha', 'ah'));
        $this->assertSame('a]ab[b', StringHelper::between('[a]ab[b]', '[', ']'));
        $this->assertSame('foo', StringHelper::between('foofoobar', 'foo', 'bar'));
        $this->assertSame('bar', StringHelper::between('foobarbar', 'foo', 'bar'));
        $this->assertSame('234', StringHelper::between('12345', 1, 5));
        $this->assertSame('45', StringHelper::between('123456789', '123', '6789'));
        $this->assertSame('nothing', StringHelper::between('nothing', 'foo', 'bar'));
    }

    public function test_str_before(): void
    {
        $this->assertSame('han', StringHelper::before('hannah', 'nah'));
        $this->assertSame('ha', StringHelper::before('hannah', 'n'));
        $this->assertSame('ééé ', StringHelper::before('ééé hannah', 'han'));
        $this->assertSame('hannah', StringHelper::before('hannah', 'xxxx'));
        $this->assertSame('hannah', StringHelper::before('hannah', ''));
        $this->assertSame('han', StringHelper::before('han0nah', '0'));
        $this->assertSame('han', StringHelper::before('han0nah', 0));
        $this->assertSame('han', StringHelper::before('han2nah', 2));
        $this->assertSame('', StringHelper::before('', ''));
        $this->assertSame('', StringHelper::before('', 'a'));
        $this->assertSame('', StringHelper::before('a', 'a'));
        $this->assertSame('foo', StringHelper::before('foo@bar.com', '@'));
        $this->assertSame('foo', StringHelper::before('foo@@bar.com', '@'));
        $this->assertSame('', StringHelper::before('@foo@bar.com', '@'));
    }

    public function test_str_before_last(): void
    {
        $this->assertSame('yve', StringHelper::beforeLast('yvette', 'tte'));
        $this->assertSame('yvet', StringHelper::beforeLast('yvette', 't'));
        $this->assertSame('ééé ', StringHelper::beforeLast('ééé yvette', 'yve'));
        $this->assertSame('', StringHelper::beforeLast('yvette', 'yve'));
        $this->assertSame('yvette', StringHelper::beforeLast('yvette', 'xxxx'));
        $this->assertSame('yvette', StringHelper::beforeLast('yvette', ''));
        $this->assertSame('yv0et', StringHelper::beforeLast('yv0et0te', '0'));
        $this->assertSame('yv0et', StringHelper::beforeLast('yv0et0te', 0));
        $this->assertSame('yv2et', StringHelper::beforeLast('yv2et2te', 2));
        $this->assertSame('', StringHelper::beforeLast('', 'test'));
        $this->assertSame('', StringHelper::beforeLast('yvette', 'yvette'));
        $this->assertSame('tempest', StringHelper::beforeLast('tempest framework', ' '));
        $this->assertSame('yvette', StringHelper::beforeLast("yvette\tyv0et0te", "\t"));
    }
}
