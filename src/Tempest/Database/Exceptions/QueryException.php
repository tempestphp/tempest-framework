<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;
use PDOException;
use Tempest\Database\Query;

final class QueryException extends Exception
{
    public function __construct(Query $query, array $bindings, PDOException $previous)
    {
        $message = $previous->getMessage();

        $message .= PHP_EOL . PHP_EOL . $query->getSql() . PHP_EOL;
        
        $message .= PHP_EOL . 'bindings: ' . json_encode($bindings, JSON_PRETTY_PRINT);

        parent::__construct(
            message: $message,
            previous: $previous,
        );
    }
}
