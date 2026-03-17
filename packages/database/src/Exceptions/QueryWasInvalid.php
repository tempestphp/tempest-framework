<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use PDOException;
use Tempest\Core\ProvidesContext;
use Tempest\Database\Query;

final class QueryWasInvalid extends Exception implements ProvidesContext
{
    public function __construct(
        private(set) Query $query,
        private(set) array $bindings,
        public readonly PDOException $pdoException,
    ) {
        $message = $this->pdoException->getMessage();
        $message .= PHP_EOL . PHP_EOL . $query->toRawSql();

        parent::__construct(
            message: $message,
            previous: $this->pdoException,
        );
    }

    public function context(): iterable
    {
        return [
            'query' => $this->query,
            'bindings' => $this->bindings,
            'raw_query' => $this->query->toRawSql(),
        ];
    }
}
