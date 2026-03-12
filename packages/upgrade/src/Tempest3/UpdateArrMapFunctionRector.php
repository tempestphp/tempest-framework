<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateArrMapFunctionRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            UseItem::class,
            FuncCall::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof UseItem) {
            if ($node->name->toString() === 'Tempest\Support\Arr\map_iterable') {
                $node->name = new Name('Tempest\Support\Arr\map');
            }

            return null;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\Support\Arr\map_iterable') {
                $node->name = new FullyQualified('Tempest\Support\Arr\map');

                return null;
            }
        }

        return null;
    }
}
