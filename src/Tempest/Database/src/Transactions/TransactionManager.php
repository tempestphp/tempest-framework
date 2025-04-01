<?php

declare(strict_types=1);

namespace Tempest\Database\Transactions;

use Tempest\Container\AllowDynamicTags;

#[AllowDynamicTags]
interface TransactionManager
{
    public function begin(): void;

    public function commit(): void;

    public function rollback(): void;
}
