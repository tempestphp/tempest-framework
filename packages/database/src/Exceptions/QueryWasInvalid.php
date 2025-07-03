<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use PDOException;
use Tempest\Database\Query;
use Tempest\Support\Json;

final class QueryWasInvalid extends Exception
{
    public function __construct(Query $query, array $bindings, PDOException $previous)
    {
        $message = $previous->getMessage();

        $message .= PHP_EOL . PHP_EOL . $query->toSql() . PHP_EOL;

        $message .= PHP_EOL . 'bindings: ' . Json\encode($bindings, pretty: true);

        parent::__construct(
            message: $message,
            previous: $previous,
        );
    }
}
