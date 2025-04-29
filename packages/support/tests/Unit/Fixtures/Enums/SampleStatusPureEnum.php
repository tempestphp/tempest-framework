<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Unit\Fixtures\Enums;

use Tempest\Support\IsEnumHelper;

enum SampleStatusPureEnum
{
    use IsEnumHelper;

    case PUBLISH;
    case DRAFT;
    case TRASH;
}
