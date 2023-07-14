<?php

namespace Tempest\ORM;

use Tempest\Interfaces\Model;

final class Migration implements Model
{
    use BaseModel;

    public string $name;
}
