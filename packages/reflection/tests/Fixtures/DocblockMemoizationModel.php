<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests\Fixtures;

final class DocblockMemoizationModel
{
    /** @var \stdClass[] */
    public array $objects;

    /** @var int[] */
    public array $integers;

    /** @var string */
    public string $notIterable;
}
