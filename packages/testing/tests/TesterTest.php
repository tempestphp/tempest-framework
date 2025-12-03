<?php

namespace Tempest\Testing\Tests;

use Exception;
use Tempest\DateTime\Exception\InvalidArgumentException;
use Tempest\Testing\Provide;
use Tempest\Testing\Test;

use function Tempest\Testing\test;

final class TesterTest
{
    #[Test]
    public function fail(): void
    {
        test(fn () => test()->fail())->fails();
    }

    #[Test]
    public function succeed(): void
    {
        test(fn () => test()->succeed())->succeeds();
    }

    #[Test]
    public function is(): void
    {
        test(fn () => test(1)->is(1))->succeeds();
        test(fn () => test(1)->is(2))->fails('failed asserting that `1` is `2`');
        test(fn () => test(1)->is('1'))->fails("failed asserting that `1` is `'1'`");
        test(fn () => test(0)->is(''))->fails();

        $a = (object) [];
        $b = (object) [];
        $c = (object) ['a' => 'a'];

        test(fn () => test($a)->is($a))->succeeds();
        test(fn () => test($a)->is($b))->fails('failed asserting that `stdClass` is `stdClass`');
        test(fn () => test($a)->is($c))->fails('failed asserting that `stdClass` is `stdClass`');
    }

    #[Test]
    public function isNot(): void
    {
        test(fn () => test(1)->isNot(1))->fails('failed asserting that `1` is not `1`');
        test(fn () => test(1)->isNot(2))->succeeds();
        test(fn () => test(1)->isNot('1'))->succeeds();
        test(fn () => test(0)->isNot(''))->succeeds();

        $a = (object) [];
        $b = (object) [];
        $c = (object) ['a' => 'a'];

        test(fn () => test($a)->isNot($a))->fails('failed asserting that `stdClass` is not `stdClass`');
        test(fn () => test($a)->isNot($b))->succeeds();
        test(fn () => test($a)->isNot($c))->succeeds();
    }

    #[
        Test,
        Provide(
            ['test' => 1, 'expected' => 1, 'succeeds' => true],
            ['test' => 1, 'expected' => 2, 'succeeds' => false],
            ['test' => 1, 'expected' => '1', 'succeeds' => true],
            ['test' => false, 'expected' => '', 'succeeds' => true],
        ),
    ]
    public function isEqualTo(mixed $test, mixed $expected, bool $succeeds): void
    {
        if ($succeeds) {
            test(fn () => test($test)->isEqualTo($expected))->succeeds();
        } else {
            test(fn () => test($test)->isEqualTo($expected))->fails();
        }
    }

    #[Test]
    public function isEqualToObject(): void
    {
        $a = (object) [];
        $b = (object) [];
        $c = (object) ['a' => 'a'];

        test(fn () => test($a)->isEqualTo($a))->succeeds();
        test(fn () => test($a)->isEqualTo($b))->succeeds();
        test(fn () => test($a)->isEqualTo($c))->fails();
    }

    #[
        Test,
        Provide(
            ['test' => 1, 'expected' => 1, 'succeeds' => false],
            ['test' => 1, 'expected' => 2, 'succeeds' => true],
            ['test' => 1, 'expected' => '1', 'succeeds' => false],
            ['test' => false, 'expected' => '', 'succeeds' => false],
        ),
    ]
    public function isNotEqualTo(mixed $test, mixed $expected, bool $succeeds): void
    {
        if ($succeeds) {
            test(fn () => test($test)->isNotEqualTo($expected))->succeeds();
        } else {
            test(fn () => test($test)->isNotEqualTo($expected))->fails();
        }
    }

    #[Test]
    public function isNotEqualToObject(): void
    {
        $a = (object) [];
        $b = (object) [];
        $c = (object) ['a' => 'a'];

        test(fn () => test($a)->isNotEqualTo($a))->fails();
        test(fn () => test($a)->isNotEqualTo($b))->fails();
        test(fn () => test($a)->isNotEqualTo($c))->succeeds();
    }

    #[Test]
    public function isCallable(): void
    {
        test(fn () => test(fn () => true)->isCallable())->succeeds();
        test(fn () => test('a')->isCallable())->fails("failed asserting that `'a'` is callable");
    }

    #[Test]
    public function isNotCallable(): void
    {
        test(fn () => test(fn () => true)->isNotCallable())->fails('failed asserting that `Closure` is not callable');
        test(fn () => test('not_callable')->isNotCallable())->succeeds();
    }

    #[Test]
    public function hasCount(): void
    {
        test(fn () => test([1, 2, 3])->hasCount(3))->succeeds();
        test(fn () => test([1, 2, 3])->hasCount(4))->fails('failed asserting that `array` has `4` items');
        test(fn () => test(1)->hasCount(4))->fails('failed asserting that `1` is countable');
    }

    #[Test]
    public function hasNotCount(): void
    {
        test(fn () => test([1, 2, 3])->hasNotCount(3))->fails('failed asserting that `array` does not have `3` items');
        test(fn () => test([1, 2, 3])->hasNotCount(4))->succeeds();
        test(fn () => test(1)->hasNotCount(4))->fails('failed asserting that `1` is countable');
    }

    #[Test]
    public function contains(): void
    {
        test(fn () => test([1, 2, 3])->contains(2))->succeeds();
        test(fn () => test([1, 2, 3])->contains(4))->fails('failed asserting that `array` contains `4`');
        test(fn () => test('abc')->contains('b'))->succeeds();
        test(fn () => test('abc')->contains('d'))->fails("failed asserting that `'abc'` contains `'d'`");
        test(fn () => test(1)->contains('d'))->fails('to check contains, the test subject must be a string or an array; instead got `1`');
    }

    #[Test]
    public function containsNot(): void
    {
        test(fn () => test([1, 2, 3])->containsNot(2))->fails('failed asserting that `array` does not contain `2`');
        test(fn () => test([1, 2, 3])->containsNot(4))->succeeds();
        test(fn () => test('abc')->containsNot('b'))->fails("failed asserting that `'abc'` does not contain `'b'`");
        test(fn () => test('abc')->containsNot('d'))->succeeds();
        test(fn () => test(1)->containsNot('d'))->fails('to check contains, the test subject must be a string or an array; instead got `1`');
    }

    #[Test]
    public function hasKey(): void
    {
        test(fn () => test([1, 2, 3])->hasKey(2))->succeeds();
        test(fn () => test([1, 2, 3])->hasKey(4))->fails('failed asserting that `array` has key `4`');
        test(fn () => test(1)->hasKey(4))->fails('failed asserting that `1` is array');
    }

    #[Test]
    public function missesKey(): void
    {
        test(fn () => test([1, 2, 3])->missesKey(2))->fails('failed asserting that `array` does not have key `2`');
        test(fn () => test([1, 2, 3])->missesKey(4))->succeeds();
        test(fn () => test(1)->missesKey(4))->fails('failed asserting that `1` is array');
    }

    #[Test]
    public function instanceOf(): void
    {
        test(fn () => test($this)->instanceOf(self::class))->succeeds();
        test(fn () => test('')->instanceOf(self::class))->fails("failed asserting that `''` is an instance of `'Tempest\\\\Testing\\\\Tests\\\\TesterTest'`");
    }

    #[Test]
    public function notInstanceOf(): void
    {
        test(fn () => test($this)->isNotInstanceOf(self::class))
            ->fails("failed asserting that `Tempest\\Testing\\Tests\\TesterTest` is not an instance of `'Tempest\\\\Testing\\\\Tests\\\\TesterTest'`");
        test(fn () => test('')->isNotInstanceOf(self::class))->succeeds();
    }

    #[Test]
    public function exceptionThrown(): void
    {
        test(function () {
            test(fn () => throw new Exception())->exceptionThrown(Exception::class);
        })->succeeds();

        test(function () {
            test(fn () => throw new InvalidArgumentException())->exceptionThrown(Exception::class);
        })->succeeds();

        test(function () {
            test(fn () => throw new Exception())->exceptionThrown(InvalidArgumentException::class);
        })->fails("Expected exception `'Tempest\\\\DateTime\\\\Exception\\\\InvalidArgumentException'` was not thrown, instead got `'Exception'`");

        test(function () {
            test(fn () => true)->exceptionThrown(InvalidArgumentException::class);
        })->fails("Expected exception `'Tempest\\\\DateTime\\\\Exception\\\\InvalidArgumentException'` was not thrown");

        test(function () {
            test()->exceptionThrown(InvalidArgumentException::class);
        })->fails('to test exceptions, the test subject must be a callable; instead got `NULL`');
    }

    #[Test]
    public function exceptionNotThrown(): void
    {
        test(function () {
            test(fn () => throw new Exception())->exceptionNotThrown(Exception::class);
        })->fails("Exception `'Exception'` was thrown, while it shouldn't");

        test(function () {
            test(fn () => throw new InvalidArgumentException())->exceptionNotThrown(Exception::class);
        })->fails("Exception `'Tempest\\\\DateTime\\\\Exception\\\\InvalidArgumentException'` was thrown, while it shouldn't");

        test(function () {
            test(fn () => throw new Exception())->exceptionNotThrown(InvalidArgumentException::class);
        })->succeeds();

        test(function () {
            test()->exceptionNotThrown(InvalidArgumentException::class);
        })->succeeds();
    }

    #[Test]
    public function isCountable(): void
    {
        test(fn () => test([1, 2])->isCountable())->succeeds();
        test(fn () => test('a')->isCountable())->fails("failed asserting that `'a'` is countable");
    }

    #[Test]
    public function isNotCountable(): void
    {
        test(fn () => test([1, 2])->isNotCountable())->fails('failed asserting that `array` is not countable');
        test(fn () => test('a')->isNotCountable())->succeeds();
    }

    #[Test]
    public function startsWith(): void
    {
        test(fn () => test('abc')->startsWith('ab'))->succeeds();
        test(fn () => test('abc')->startsWith('zz'))->fails("failed asserting that `'abc'` starts with `'zz'`");
        test(fn () => test(1)->startsWith('zz'))->fails('failed asserting that `1` is string');
    }

    #[Test]
    public function endsWith(): void
    {
        test(fn () => test('abc')->endsWith('bc'))->succeeds();
        test(fn () => test('abc')->endsWith('zz'))->fails("failed asserting that `'abc'` ends with `'zz'`");
        test(fn () => test(1)->endsWith('zz'))->fails('failed asserting that `1` is string');
    }

    #[Test]
    public function startsNotWith(): void
    {
        test(fn () => test('abc')->startsNotWith('ab'))->fails("failed asserting that `'abc'` does not start with `'ab'`");
        test(fn () => test('abc')->startsNotWith('zz'))->succeeds();
        test(fn () => test(1)->startsNotWith('zz'))->fails('failed asserting that `1` is string');
    }

    #[Test]
    public function endsNotWith(): void
    {
        test(fn () => test('abc')->endsNotWith('bc'))->fails("failed asserting that `'abc'` does not end with `'bc'`");
        test(fn () => test('abc')->endsNotWith('zz'))->succeeds();
        test(fn () => test(1)->endsNotWith('zz'))->fails('failed asserting that `1` is string');
    }

    #[Test]
    public function isList(): void
    {
        test(fn () => test([1, 2, 3])->isList())->succeeds();
        test(fn () => test([1 => 'a'])->isList())->fails('failed asserting that `array` is a list');
        test(fn () => test('a')->isList())->fails('failed asserting that `\'a\'` is array');
    }

    #[Test]
    public function isNotList(): void
    {
        test(fn () => test([1, 2, 3])->isNotList())->fails('failed asserting that `array` is not a list');
        test(fn () => test([1 => 'a'])->isNotList())->succeeds();
        test(fn () => test('a')->isNotList())->fails('failed asserting that `\'a\'` is array');
    }

    #[Test]
    public function isEmpty(): void
    {
        test(fn () => test([])->isEmpty())->succeeds();
        test(fn () => test('a')->isEmpty())->fails("failed asserting that `'a'` is empty");
    }

    #[Test]
    public function isNotEmpty(): void
    {
        test(fn () => test('a')->isNotEmpty())->succeeds();
        test(fn () => test('')->isNotEmpty())->fails("failed asserting that `''` is not empty");
    }

    #[Test]
    public function greaterThan(): void
    {
        test(fn () => test(5)->greaterThan(4))->succeeds();
        test(fn () => test(5)->greaterThan(5))->fails('failed asserting that `5` is greater than `5`');
        test(fn () => test('a')->greaterThan(4))->fails('failed asserting that `\'a\'` is numeric');
    }

    #[Test]
    public function greaterThanOrEqual(): void
    {
        test(fn () => test(5)->greaterThanOrEqual(5))->succeeds();
        test(fn () => test(4)->greaterThanOrEqual(5))->fails('failed asserting that `4` is greater than or equal to `5`');
        test(fn () => test('a')->greaterThanOrEqual(4))->fails('failed asserting that `\'a\'` is numeric');
    }

    #[Test]
    public function lessThan(): void
    {
        test(fn () => test(4)->lessThan(5))->succeeds();
        test(fn () => test(5)->lessThan(5))->fails('failed asserting that `5` is less than `5`');
        test(fn () => test('a')->lessThan(4))->fails('failed asserting that `\'a\'` is numeric');
    }

    #[Test]
    public function lessThanOrEqual(): void
    {
        test(fn () => test(5)->lessThanOrEqual(5))->succeeds();
        test(fn () => test(6)->lessThanOrEqual(5))->fails('failed asserting that `6` is less than or equal to `5`');
        test(fn () => test('a')->lessThanOrEqual(4))->fails('failed asserting that `\'a\'` is numeric');
    }

    #[Test]
    public function isTrue(): void
    {
        test(fn () => test(true)->isTrue())->succeeds();
        test(fn () => test(false)->isTrue())->fails('failed asserting that `false` is true');
    }

    #[Test]
    public function isFalse(): void
    {
        test(fn () => test(false)->isFalse())->succeeds();
        test(fn () => test(true)->isFalse())->fails('failed asserting that `true` is false');
    }

    #[Test]
    public function isTrueish(): void
    {
        test(fn () => test(1)->isTrueish())->succeeds();
        test(fn () => test(0)->isTrueish())->fails('failed asserting that `0` is trueish');
    }

    #[Test]
    public function isFalseish(): void
    {
        test(fn () => test(0)->isFalseish())->succeeds();
        test(fn () => test(1)->isFalseish())->fails('failed asserting that `1` is falseish');
    }

    #[Test]
    public function isNull(): void
    {
        test(fn () => test(null)->isNull())->succeeds();
        test(fn () => test(0)->isNull())->fails('failed asserting that `0` is null');
    }

    #[Test]
    public function isNotNull(): void
    {
        test(fn () => test(0)->isNotNull())->succeeds();
        test(fn () => test(null)->isNotNull())->fails('failed asserting that `NULL` is not null');
    }

    #[Test]
    public function isArray(): void
    {
        test(fn () => test([1])->isArray())->succeeds();
        test(fn () => test(1)->isArray())->fails('failed asserting that `1` is array');
    }

    #[Test]
    public function isNotArray(): void
    {
        test(fn () => test([1])->isNotArray())->fails('failed asserting that `array` is not array');
        test(fn () => test(1)->isNotArray())->succeeds();
    }

    #[Test]
    public function isBool(): void
    {
        test(fn () => test(true)->isBool())->succeeds();
        test(fn () => test(1)->isBool())->fails('failed asserting that `1` is bool');
    }

    #[Test]
    public function isNotBool(): void
    {
        test(fn () => test(true)->isNotBool())->fails('failed asserting that `true` is not bool');
        test(fn () => test(1)->isNotBool())->succeeds();
    }

    #[Test]
    public function isFloat(): void
    {
        test(fn () => test(1.2)->isFloat())->succeeds();
        test(fn () => test(1)->isFloat())->fails('failed asserting that `1` is float');
    }

    #[Test]
    public function isNotFloat(): void
    {
        test(fn () => test(1.2)->isNotFloat())->fails('failed asserting that `1.2` is not float');
        test(fn () => test(1)->isNotFloat())->succeeds();
    }

    #[Test]
    public function isInt(): void
    {
        test(fn () => test(1)->isInt())->succeeds();
        test(fn () => test(1.1)->isInt())->fails('failed asserting that `1.1` is int');
    }

    #[Test]
    public function isNotInt(): void
    {
        test(fn () => test(1)->isNotInt())->fails('failed asserting that `1` is not int');
        test(fn () => test(1.1)->isNotInt())->succeeds();
    }

    #[Test]
    public function isNumeric(): void
    {
        test(fn () => test('1')->isNumeric())->succeeds();
        test(fn () => test('a')->isNumeric())->fails("failed asserting that `'a'` is numeric");
    }

    #[Test]
    public function isNotNumeric(): void
    {
        test(fn () => test('1')->isNotNumeric())->fails("failed asserting that `'1'` is not numeric");
        test(fn () => test('a')->isNotNumeric())->succeeds();
    }

    #[Test]
    public function isObject(): void
    {
        test(fn () => test((object) [])->isObject())->succeeds();
        test(fn () => test(1)->isObject())->fails('failed asserting that `1` is object');
    }

    #[Test]
    public function isNotObject(): void
    {
        test(fn () => test((object) [])->isNotObject())->fails('failed asserting that `stdClass` is not object');
        test(fn () => test(1)->isNotObject())->succeeds();
    }

    #[Test]
    public function isResource(): void
    {
        $res = fopen('php://temp', 'r');
        test(fn () => test($res)->isResource())->succeeds();
        test(fn () => test(1)->isResource())->fails('failed asserting that `1` is resource');
        fclose($res);
    }

    #[Test]
    public function isNotResource(): void
    {
        $res = fopen('php://temp', 'r');
        test(fn () => test($res)->isNotResource())->fails('failed asserting that `resource` is not resource');
        test(fn () => test(1)->isNotResource())->succeeds();
        fclose($res);
    }

    #[Test]
    public function isString(): void
    {
        test(fn () => test('a')->isString())->succeeds();
        test(fn () => test(1)->isString())->fails('failed asserting that `1` is string');
    }

    #[Test]
    public function isNotString(): void
    {
        test(fn () => test('a')->isNotString())->fails("failed asserting that `'a'` is not string");
        test(fn () => test(1)->isNotString())->succeeds();
    }

    #[Test]
    public function isScalar(): void
    {
        test(fn () => test(1)->isScalar())->succeeds();
        test(fn () => test([])->isScalar())->fails('failed asserting that `array` is scalar');
    }

    #[Test]
    public function isNotScalar(): void
    {
        test(fn () => test(1)->isNotScalar())->fails('failed asserting that `1` is not scalar');
        test(fn () => test([])->isNotScalar())->succeeds();
    }

    #[Test]
    public function isIterable(): void
    {
        test(fn () => test([1])->isIterable())->succeeds();
        test(fn () => test(1)->isIterable())->fails('failed asserting that `1` is iterable');
    }

    #[Test]
    public function isNotIterable(): void
    {
        test(fn () => test([1])->isNotIterable())->fails('failed asserting that `array` is not iterable');
        test(fn () => test(1)->isNotIterable())->succeeds();
    }

    #[Test]
    public function isJson(): void
    {
        test(fn () => test('{"a":1}')->isJson())->succeeds();
        test(fn () => test('not json')->isJson())->fails("failed asserting that `'not json'` is valid JSON");
        test(fn () => test(1)->isJson())->fails('failed asserting that `1` is string');
    }

    #[Test]
    public function isNotJson(): void
    {
        test(fn () => test('{"a":1}')->isNotJson())->fails("failed asserting that `'{\"a\":1}'` is not valid JSON");
        test(fn () => test('not json')->isNotJson())->succeeds();
        test(fn () => test(1)->isNotJson())->succeeds();
    }
}
