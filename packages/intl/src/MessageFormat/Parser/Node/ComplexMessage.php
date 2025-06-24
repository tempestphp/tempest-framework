<?php

namespace Tempest\Intl\MessageFormat\Parser\Node;

use Tempest\Intl\MessageFormat\Parser\Node\ComplexBody\ComplexBody;

final class ComplexMessage extends MessageNode
{
    public function __construct(
        /** @var Declaration[] */
        public readonly array $declarations,
        public readonly ComplexBody $body,
    ) {
        parent::__construct($body->getPattern());
    }
}
