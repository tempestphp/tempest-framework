<?php

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\Id;

final class Publisher
{
    public Id $id;

    public string $name;

    public string $description;
}
