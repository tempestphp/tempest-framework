<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateArrMapFunctionRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\Support\Arr\map_iterable') {
                $node->name = new Node\Name('Tempest\Support\Arr\map');
            }

            return null;
        }

        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\Support\Arr\map_iterable') {
                $node->name = new Node\Name\FullyQualified('Tempest\Support\Arr\map');

                return null;
            }
        }

        return null;
    }
}
