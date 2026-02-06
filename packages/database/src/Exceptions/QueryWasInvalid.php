<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use PDOException;
use Tempest\Core\ProvidesContext;
use Tempest\Database\Query;

final class QueryWasInvalid extends Exception implements ProvidesContext
{
    public readonly PDOException $pdoException;

    public function __construct(
        private(set) Query $query,
        private(set) array $bindings,
        PDOException $previous,
    ) {
        $this->pdoException = $previous;

        $message = $previous->getMessage();
        $message .= PHP_EOL . PHP_EOL . $query->toRawSql();

        parent::__construct(
            message: $message,
            previous: $previous,
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
