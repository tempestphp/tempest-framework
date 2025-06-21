<?php

namespace Tempest\Internationalization\MessageFormat\Parser\Node\ComplexBody;

use Tempest\Internationalization\MessageFormat\Parser\Node\Node;
use Tempest\Internationalization\MessageFormat\Parser\Node\Pattern\Pattern;

interface ComplexBody extends Node
{
    public function getPattern(): Pattern;
}
