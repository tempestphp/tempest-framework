<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateContainerFunctionImportsRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\UseItem::class,
            Node\Expr\FuncCall::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof Node\UseItem) {
            if ($node->name->toString() === 'Tempest\get') {
                $node->name = new Node\Name('Tempest\Container\get');
            }

            if ($node->name->toString() === 'Tempest\invoke') {
                $node->name = new Node\Name('Tempest\Container\invoke');
            }
        }

        return null;
    }
}
