<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Fixtures;

enum BookStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case FEATURED = 'featured';
}
