<?php

declare(strict_types=1);

namespace Tempest\Database\Migrations;

use Tempest\Database\IsModel;
use Tempest\Database\Model;

final class Migration implements Model
{
    use IsModel;

    public string $name;
}
