<?php

declare(strict_types=1);

namespace Tempest\DateTime\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tempest\DateTime\Exception\InvalidArgumentException;

final class InvalidArgumentExceptionTest extends TestCase
{
    public function test_for_year(): void
    {
        $exception = InvalidArgumentException::forYear(-1);

        $this->assertSame(
            "The year '-1' diverges from expectation; a positive integer is required.",
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);

        throw $exception;
    }

    public function test_for_month(): void
    {
        $exception = InvalidArgumentException::forMonth(13);

        $this->assertSame(
            "The month '13' falls outside the acceptable range of '1' to '12'.",
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);

        throw $exception;
    }

    public function test_for_day(): void
    {
        $exception = InvalidArgumentException::forDay(32, 1, 2021);

        $this->assertSame(
            "The day '32', for month '1' and year '2021', does not align with the expected range of '1' to '31'.",
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);

        throw $exception;
    }

    public function test_for_hours(): void
    {
        $exception = InvalidArgumentException::forHours(24);

        $this->assertSame("The hour '24' exceeds the expected range of '0' to '23'.", $exception->getMessage());

        $this->expectExceptionObject($exception);

        throw $exception;
    }

    public function test_for_minutes(): void
    {
        $exception = InvalidArgumentException::forMinutes(60);

        $this->assertSame("The minute '60' steps beyond the bounds of '0' to '59'.", $exception->getMessage());

        $this->expectExceptionObject($exception);

        throw $exception;
    }

    public function test_for_seconds(): void
    {
        $exception = InvalidArgumentException::forSeconds(61);

        $this->assertSame(
            "The seconds '61' stretch outside the acceptable range of '0' to '59'.",
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);

        throw $exception;
    }

    public function test_for_nanoseconds(): void
    {
        $exception = InvalidArgumentException::forNanoseconds(1_000_000_000);

        $this->assertSame(
            "The nanoseconds '1000000000' exceed the foreseen limit of '0' to '999999999'.",
            $exception->getMessage(),
        );

        $this->expectExceptionObject($exception);

        throw $exception;
    }
}
