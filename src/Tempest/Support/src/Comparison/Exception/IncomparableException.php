<?php

declare(strict_types=1);

namespace Tempest\Support\Comparison\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;

use function get_debug_type;

final class IncomparableException extends PhpInvalidArgumentException
{
    public static function fromValues(mixed $a, mixed $b, string $additionalInfo = ''): self
    {
        return new self(sprintf(
            'Unable to compare "%s" with "%s"%s',
            get_debug_type($a),
            get_debug_type($b),
            $additionalInfo ? (': ' . $additionalInfo) : '.',
        ));
    }
}
