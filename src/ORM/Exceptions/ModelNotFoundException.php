<?php

declare(strict_types=1);

namespace Tempest\ORM\Exceptions;

use Exception;
use Tempest\Database\Id;

class ModelNotFoundException extends Exception
{
    public static function new(?Id $id = null): self
    {
        if ($id !== null) {
            return new self("Model with ID {$id} not found");
        }

        return new self("Model not found");
    }
}
