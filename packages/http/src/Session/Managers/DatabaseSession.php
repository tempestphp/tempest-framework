<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Database\PrimaryKey;
use Tempest\Database\Table;
use Tempest\DateTime\DateTime;

#[Table('sessions')]
final class DatabaseSession
{
    public PrimaryKey $id;

    public string $session_id;

    public string $data;

    public DateTime $created_at;

    public DateTime $last_active_at;
}
