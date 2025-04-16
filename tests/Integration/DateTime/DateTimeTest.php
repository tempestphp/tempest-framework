<?php

namespace Tests\Tempest\Integration\DateTime;

use PHPUnit\Framework\Attributes\TestWith;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tests\Tempest\Integration\DateTime\Fixtures\ClassUsingDateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\DateTime\now;

final class DateTimeTest extends FrameworkIntegrationTestCase
{
    public function test_set_now_using_string(): void
    {
        $this->datetime->setNow('2024-01-01');

        $now = $this->container->get(DateTimeInterface::class);

        $this->assertTrue($now->equals('2024-01-01'));
    }

    #[TestWith([DateTimeInterface::class])]
    #[TestWith([DateTime::class])]
    public function test_set_now_location(string $fqcn): void
    {
        $now = $this->container->get(DateTimeInterface::class);

        $this->datetime->setNow($now->minusDays(3));

        $date = $this->container->get($fqcn);

        $this->assertTrue($now->minusDays(3)->equals($date));
    }

    public function test_set_now_injection(): void
    {
        $now = $this->container->get(DateTimeInterface::class);

        $this->datetime->setNow($now->minusDays(3));

        $class = $this->container->get(ClassUsingDateTime::class);

        $this->assertTrue($now->minusDays(3)->equals($class->now));
    }

    public function test_set_now_named_construtor(): void
    {
        $this->datetime->setNow('2024-01-01');

        $now = DateTime::now();

        $this->assertTrue($now->equals('2024-01-01'));
    }

    public function test_set_now_function(): void
    {
        $this->datetime->setNow('2024-01-01');

        $this->assertTrue(now()->equals('2024-01-01'));
    }
}
