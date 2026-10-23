<?php

namespace Tempest\Database\Columns;

final class Uuid implements Column
{
    use IsColumn;
    use HasConstraints;
}
