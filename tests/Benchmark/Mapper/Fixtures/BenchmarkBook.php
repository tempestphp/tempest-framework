<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Mapper\Fixtures;

use DateTimeImmutable;

final class BenchmarkBook
{
    public int $id;

    public string $title;

    public string $description;

    public bool $published;

    public float $price;

    public DateTimeImmutable $created_at;
}
