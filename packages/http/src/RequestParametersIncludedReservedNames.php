<?php

namespace Tempest\Http;

use Exception;
use Tempest\Support\Arr\ImmutableArray;

final class RequestParametersIncludedReservedNames extends Exception
{
    public function __construct(string $requestClass, ImmutableArray $reservedProperties)
    {
        $message = sprintf(
            'The request payload included data with reserved property names: %s. It could not be mapped to `%s`',
            $reservedProperties->join('`, `')->wrap('`'),
            $requestClass,
        );

        parent::__construct($message);
    }
}
