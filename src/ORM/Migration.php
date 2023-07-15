<?php

declare(strict_types=1);

namespace Tempest\ORM;

use Tempest\Interfaces\Model;

final class Migration implements Model
{
    use BaseModel;

    public string $name;
}
