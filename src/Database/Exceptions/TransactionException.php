<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class TransactionException extends Exception
{
    public static function transactionFailed(): self
    {
        return new self('Transaction failed');
    }

    public static function beginFailed(): self
    {
        return new self('Transaction begin failed');
    }

    public static function commitFailed(): self
    {
        return new self('Transaction commit failed');
    }
}
