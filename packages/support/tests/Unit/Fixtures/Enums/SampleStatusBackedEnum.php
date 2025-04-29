<?php

declare(strict_types=1);

namespace Tempest\Support\Tests\Unit\Fixtures\Enums;

use Tempest\Support\IsEnumHelper;

enum SampleStatusBackedEnum: string
{
    use IsEnumHelper;

    case PUBLISH = 'publish';
    case DRAFT = 'draft';
    case TRASH = 'trash';
}
