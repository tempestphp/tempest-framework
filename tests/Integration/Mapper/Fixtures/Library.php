<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tests\Tempest\Integration\Mapper\Fixtures\Children\LibraryItem;

final class Library
{
    /** @var LibraryItem[] */
    public array $items = [];
}
