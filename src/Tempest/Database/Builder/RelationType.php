<?php

namespace Tempest\Database\Builder;

enum RelationType
{
    case BELONGS_TO;
    case HAS_MANY;
}
