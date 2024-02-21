<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\ORM\IsModel;
use Tempest\ORM\Model;

final class Migration implements Model
{
    use IsModel;

    public string $name;
}
