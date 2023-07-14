<?php

namespace Tempest\Database\TableBuilder;

enum TableBuilderAction
{
    case DROP;
    case CREATE;
    case ALTER;
}
