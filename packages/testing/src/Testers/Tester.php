<?php

namespace Tempest\Testing\Testers;

use Closure;
use Tempest\Testing\Exceptions\TestHasFailed;
use Throwable;

use function Tempest\Testing\test;

final readonly class Tester
{
    public function __construct(
        private mixed $subject = null,
    ) {}

    public function dump(): self
    {
        lw($this->subject);

        return $this;
    }

    public function fail(?string $reason = null): never
    {
        throw new TestHasFailed($reason ?? 'test was marked as failed');
    }

    public function succeed(): void
    {
        return;
    }

    public function fails(?string $message = null): self
    {
        $exceptionTester = null;

        if ($message) {
            $exceptionTester = function (TestHasFailed $exception) use ($message) {
                test($exception->getMessage())->is($message);
            };
        }

        $this->exceptionThrown(
            expectedExceptionClass: TestHasFailed::class,
            exceptionTester: $exceptionTester,
        );

        return $this;
    }

    public function succeeds(): self
    {
        $this->isCallable();

        ($this->subject)();

        return $this;
    }

    public function is(mixed $expected): self
    {
        if ($expected !== $this->subject) {
            throw new TestHasFailed('failed asserting that %s is %s', $this->subject, $expected);
        }

        return $this;
    }

    public function isNot(mixed $expected): self
    {
        if ($expected === $this->subject) {
            throw new TestHasFailed('failed asserting that %s is not %s', $this->subject, $expected);
        }

        return $this;
    }

    public function isEqualTo(mixed $expected): self
    {
        if ($expected != $this->subject) { // @mago-expect lint:identity-comparison
            throw new TestHasFailed('failed asserting that %s is equal to %s', $this->subject, $expected);
        }

        return $this;
    }

    public function isNotEqualTo(mixed $expected): self
    {
        if ($expected == $this->subject) { // @mago-expect lint:identity-comparison
            throw new TestHasFailed('failed asserting that %s is not equal to %s', $this->subject, $expected);
        }

        return $this;
    }

    public function isCallable(): self
    {
        if (! is_callable($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is callable', $this->subject);
        }

        return $this;
    }

    public function isNotCallable(): self
    {
        if (is_callable($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not callable', $this->subject);
        }

        return $this;
    }

    public function hasCount(int $expected): self
    {
        $this->isCountable();

        if ($expected !== count($this->subject)) {
            throw new TestHasFailed('failed asserting that %s has %s items', $this->subject, $expected);
        }

        return $this;
    }

    public function hasNotCount(int $expected): self
    {
        $this->isCountable();

        if ($expected === count($this->subject)) {
            throw new TestHasFailed('failed asserting that %s does not have %s items', $this->subject, $expected);
        }

        return $this;
    }

    public function isCountable(): self
    {
        if (! is_countable($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is countable', $this->subject);
        }

        return $this;
    }

    public function isNotCountable(): self
    {
        if (is_countable($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not countable', $this->subject);
        }

        return $this;
    }

    public function contains(mixed $search): self
    {
        if (! is_string($this->subject) && ! is_array($this->subject)) {
            throw new TestHasFailed('to check contains, the test subject must be a string or an array; instead got %s', $this->subject);
        }

        if (is_string($this->subject) && ! str_contains($this->subject, $search)) {
            throw new TestHasFailed('failed asserting that %s contains %s', $this->subject, $search);
        }

        if (is_array($this->subject) && ! in_array($search, $this->subject, strict: true)) {
            throw new TestHasFailed('failed asserting that %s contains %s', $this->subject, $search);
        }

        return $this;
    }

    public function containsNot(mixed $search): self
    {
        if (! is_string($this->subject) && ! is_array($this->subject)) {
            throw new TestHasFailed('to check contains, the test subject must be a string or an array; instead got %s', $this->subject);
        }

        if (is_string($this->subject) && str_contains($this->subject, $search)) {
            throw new TestHasFailed('failed asserting that %s does not contain %s', $this->subject, $search);
        }

        if (is_array($this->subject) && in_array($search, $this->subject, strict: true)) {
            throw new TestHasFailed('failed asserting that %s does not contain %s', $this->subject, $search);
        }

        return $this;
    }

    public function hasKey(mixed $key): self
    {
        $this->isArray();

        if (! array_key_exists($key, $this->subject)) {
            throw new TestHasFailed('failed asserting that %s has key %s', $this->subject, $key);
        }

        return $this;
    }

    public function missesKey(mixed $key): self
    {
        $this->isArray();

        if (array_key_exists($key, $this->subject)) {
            throw new TestHasFailed('failed asserting that %s does not have key %s', $this->subject, $key);
        }

        return $this;
    }

    public function instanceOf(string $expectedClass): self
    {
        if (! $this->subject instanceof $expectedClass) {
            throw new TestHasFailed('failed asserting that %s is an instance of %s', $this->subject, $expectedClass);
        }

        return $this;
    }

    public function isNotInstanceOf(string $expectedClass): self
    {
        if ($this->subject instanceof $expectedClass) {
            throw new TestHasFailed('failed asserting that %s is not an instance of %s', $this->subject, $expectedClass);
        }

        return $this;
    }

    public function exceptionThrown(
        string $expectedExceptionClass,
        ?Closure $exceptionTester = null,
    ): self {
        if (! is_callable($this->subject)) {
            throw new TestHasFailed('to test exceptions, the test subject must be a callable; instead got %s', $this->subject);
        }

        try {
            ($this->subject)();
        } catch (Throwable $throwable) {
            if (! $throwable instanceof $expectedExceptionClass) {
                throw new TestHasFailed('Expected exception %s was not thrown, instead got %s', $expectedExceptionClass, $throwable::class);
            }

            if ($exceptionTester) {
                $exceptionTester($throwable);
            }

            return $this;
        }

        throw new TestHasFailed('Expected exception %s was not thrown', $expectedExceptionClass);

        return $this;
    }

    public function exceptionNotThrown(string $expectedExceptionClass): self
    {
        if (! is_callable($this->subject)) {
            return $this;
        }

        try {
            ($this->subject)();
        } catch (Throwable $throwable) {
            if ($throwable instanceof $expectedExceptionClass) {
                throw new TestHasFailed("Exception %s was thrown, while it shouldn't", $throwable::class);
            }
        }

        return $this;
    }

    public function isList(): self
    {
        $this->isArray();

        if (! array_is_list($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is a list', $this->subject);
        }

        return $this;
    }

    public function isNotList(): self
    {
        $this->isArray();

        if (array_is_list($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not a list', $this->subject);
        }

        return $this;
    }

    public function isEmpty(): self
    {
        if (! empty($this->subject)) { // @mago-expect lint:no-empty
            throw new TestHasFailed('failed asserting that %s is empty', $this->subject);
        }

        return $this;
    }

    public function isNotEmpty(): self
    {
        if (empty($this->subject)) { // @mago-expect lint:no-empty
            throw new TestHasFailed('failed asserting that %s is not empty', $this->subject);
        }

        return $this;
    }

    public function greaterThan(int|float $minimum): self
    {
        $this->isNumeric();

        if ($this->subject <= $minimum) {
            throw new TestHasFailed('failed asserting that %s is greater than %s', $this->subject, $minimum);
        }

        return $this;
    }

    public function greaterThanOrEqual(int|float $minimum): self
    {
        $this->isNumeric();

        if ($this->subject < $minimum) {
            throw new TestHasFailed('failed asserting that %s is greater than or equal to %s', $this->subject, $minimum);
        }

        return $this;
    }

    public function lessThan(int|float $maximum): self
    {
        $this->isNumeric();

        if ($this->subject >= $maximum) {
            throw new TestHasFailed('failed asserting that %s is less than %s', $this->subject, $maximum);
        }

        return $this;
    }

    public function lessThanOrEqual(int|float $maximum): self
    {
        $this->isNumeric();

        if ($this->subject > $maximum) {
            throw new TestHasFailed('failed asserting that %s is less than or equal to %s', $this->subject, $maximum);
        }

        return $this;
    }

    public function isTrue(): self
    {
        if ($this->subject !== true) {
            throw new TestHasFailed('failed asserting that %s is true', $this->subject);
        }

        return $this;
    }

    public function isFalse(): self
    {
        if ($this->subject !== false) {
            throw new TestHasFailed('failed asserting that %s is false', $this->subject);
        }

        return $this;
    }

    public function isTrueish(): self
    {
        if ((bool) $this->subject !== true) {
            throw new TestHasFailed('failed asserting that %s is trueish', $this->subject);
        }

        return $this;
    }

    public function isFalseish(): self
    {
        if ((bool) $this->subject !== false) {
            throw new TestHasFailed('failed asserting that %s is falseish', $this->subject);
        }

        return $this;
    }

    public function isNull(): self
    {
        if (! is_null($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is null', $this->subject);
        }

        return $this;
    }

    public function isNotNull(): self
    {
        if (is_null($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not null', $this->subject);
        }

        return $this;
    }

    public function isArray(): self
    {
        if (! is_array($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is array', $this->subject);
        }

        return $this;
    }

    public function isNotArray(): self
    {
        if (is_array($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not array', $this->subject);
        }

        return $this;
    }

    public function isBool(): self
    {
        if (! is_bool($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is bool', $this->subject);
        }

        return $this;
    }

    public function isNotBool(): self
    {
        if (is_bool($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not bool', $this->subject);
        }

        return $this;
    }

    public function isFloat(): self
    {
        if (! is_float($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is float', $this->subject);
        }

        return $this;
    }

    public function isNotFloat(): self
    {
        if (is_float($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not float', $this->subject);
        }

        return $this;
    }

    public function isInt(): self
    {
        if (! is_int($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is int', $this->subject);
        }

        return $this;
    }

    public function isNotInt(): self
    {
        if (is_int($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not int', $this->subject);
        }

        return $this;
    }

    public function isNumeric(): self
    {
        if (! is_numeric($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is numeric', $this->subject);
        }

        return $this;
    }

    public function isNotNumeric(): self
    {
        if (is_numeric($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not numeric', $this->subject);
        }

        return $this;
    }

    public function isObject(): self
    {
        if (! is_object($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is object', $this->subject);
        }

        return $this;
    }

    public function isNotObject(): self
    {
        if (is_object($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not object', $this->subject);
        }

        return $this;
    }

    public function isResource(): self
    {
        if (! is_resource($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is resource', $this->subject);
        }

        return $this;
    }

    public function isNotResource(): self
    {
        if (is_resource($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not resource', $this->subject);
        }

        return $this;
    }

    public function isString(): self
    {
        if (! is_string($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is string', $this->subject);
        }

        return $this;
    }

    public function isNotString(): self
    {
        if (is_string($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not string', $this->subject);
        }

        return $this;
    }

    public function isScalar(): self
    {
        if (! is_scalar($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is scalar', $this->subject);
        }

        return $this;
    }

    public function isNotScalar(): self
    {
        if (is_scalar($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not scalar', $this->subject);
        }

        return $this;
    }

    public function isIterable(): self
    {
        if (! is_iterable($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is iterable', $this->subject);
        }

        return $this;
    }

    public function isNotIterable(): self
    {
        if (is_iterable($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not iterable', $this->subject);
        }

        return $this;
    }

    public function startsWith(string $prefix): self
    {
        $this->isString();

        if (! str_starts_with($this->subject, $prefix)) {
            throw new TestHasFailed('failed asserting that %s starts with %s', $this->subject, $prefix);
        }

        return $this;
    }

    public function startsNotWith(string $prefix): self
    {
        $this->isString();

        if (str_starts_with($this->subject, $prefix)) {
            throw new TestHasFailed('failed asserting that %s does not start with %s', $this->subject, $prefix);
        }

        return $this;
    }

    public function endsWith(string $suffix): self
    {
        $this->isString();

        if (! str_ends_with($this->subject, $suffix)) {
            throw new TestHasFailed('failed asserting that %s ends with %s', $this->subject, $suffix);
        }

        return $this;
    }

    public function endsNotWith(string $suffix): self
    {
        $this->isString();

        if (str_ends_with($this->subject, $suffix)) {
            throw new TestHasFailed('failed asserting that %s does not end with %s', $this->subject, $suffix);
        }

        return $this;
    }

    public function isJson(): self
    {
        $this->isString();

        if (! json_validate($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is valid JSON', $this->subject);
        }

        return $this;
    }

    public function isNotJson(): self
    {
        if (! is_string($this->subject)) {
            return $this;
        }

        if (json_validate($this->subject)) {
            throw new TestHasFailed('failed asserting that %s is not valid JSON', $this->subject);
        }

        return $this;
    }
}
