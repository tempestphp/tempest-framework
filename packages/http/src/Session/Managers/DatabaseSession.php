<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tempest\Database\Uuid;
use Tempest\DateTime\DateTime;

#[Table('sessions')]
final class DatabaseSession
{
    #[Uuid]
    public PrimaryKey $id;

    public string $data;

    public DateTime $created_at;

    public DateTime $last_active_at;
}
