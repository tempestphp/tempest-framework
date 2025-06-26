<?php

declare(strict_types=1);

namespace Tempest\Generation;

use Exception;

final class GeneratedClassWasMissingANamespace extends Exception
{
    public function __construct()
    {
        parent::__construct('A namespace is required to generate a class');
    }
}
