<?php

namespace Tempest\Intl\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Intl\MessageFormat\Parser\Node\Node;
use Tempest\Intl\MessageFormat\Parser\Node\Pattern\Pattern;

interface ComplexBody extends Node
{
    public function getPattern(): Pattern;
}
