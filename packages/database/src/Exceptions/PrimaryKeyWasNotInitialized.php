<?php

declare(strict_types=1);

namespace Tempest\Database\Exceptions;

use Exception;

final class PrimaryKeyWasNotInitialized extends Exception
{
    public function __construct(
        public readonly string $model,
    ) {
        parent::__construct("Cannot query relations on `{$model}` without a primary key value.");
    }
}
