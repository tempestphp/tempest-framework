<?php

declare(strict_types=1);

namespace Tempest\Database;

use function Tempest\get;
use Tempest\Interface\Database;

final readonly class Query
{
    public function __construct(
        public string $sql,
        public array $bindings = [],
    ) {
    }

    public function execute(): Id
    {
        $database = $this->getDatabase();

        $database->execute($this);

        return isset($this->bindings['id'])
            ? new Id($this->bindings['id'])
            : $database->getLastInsertId();
    }

    public function fetch(): array
    {
        return $this->getDatabase()->fetch($this);
    }

    public function fetchFirst(): ?array
    {
        return $this->getDatabase()->fetchFirst($this);
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    private function getDatabase(): Database
    {
        return get(Database::class);
    }
}
