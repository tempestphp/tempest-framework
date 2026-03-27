<?php

namespace Tempest\Database\Builder;

enum WhereConnector: string
{
    case AND = 'AND';
    case OR = 'OR';
}
