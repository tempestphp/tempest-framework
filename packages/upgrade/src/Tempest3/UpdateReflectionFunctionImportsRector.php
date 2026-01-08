<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateReflectionFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\reflect') {
                $node->name = new Node\Name('Tempest\Reflection\reflect');
            }
        }

        return null;
    }
}
