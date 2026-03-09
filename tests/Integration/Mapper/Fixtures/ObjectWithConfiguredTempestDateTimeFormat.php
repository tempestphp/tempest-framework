<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\DateTime\DateTime;
use Tempest\Validation\Rules\HasDateTimeFormat;

final readonly class ObjectWithConfiguredTempestDateTimeFormat
{
    public function __construct(
        #[HasDateTimeFormat('dd/MM/yyyy HH:mm:ss')]
        public DateTime $date,
    ) {}
}
