<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\DateTime\DateTime;
use Tempest\DateTime\FormatPattern;
use Tempest\Validation\Rules\HasDateTimeFormat;

final readonly class NestedObjectWithDate
{
    public function __construct(
        #[HasDateTimeFormat(FormatPattern::ISO8601)]
        public DateTime $createdAt,
    ) {}
}
