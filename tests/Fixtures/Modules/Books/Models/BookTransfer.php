<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Modules\Books\Models;

use Tempest\Database\IsDatabaseModel;

final class BookTransfer
{
    use IsDatabaseModel;

    public ?Author $sender = null;

    public ?Author $recipient = null;

    public string $reason;
}
