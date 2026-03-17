<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateMapperFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\map') {
                $node->name = new Name('Tempest\Mapper\map');
            }

            if ($node->name->toString() === 'Tempest\make') {
                $node->name = new Name('Tempest\Mapper\make');
            }

            return null;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\map') {
                $node->name = new FullyQualified('Tempest\Mapper\map');

                return null;
            }

            if ($functionName === 'Tempest\make') {
                $node->name = new FullyQualified('Tempest\Mapper\make');

                return null;
            }
        }

        return null;
    }
}
