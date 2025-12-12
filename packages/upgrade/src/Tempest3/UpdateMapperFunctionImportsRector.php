<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use Rector\Rector\AbstractRector;

final class UpdateMapperFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\map') {
                $node->name = new Node\Name('Tempest\Mapper\map');
            }

            if ($node->name->toString() === 'Tempest\make') {
                $node->name = new Node\Name('Tempest\Mapper\make');
            }

            return null;
        }

        if ($node instanceof Node\Expr\FuncCall && $node->name instanceof Node\Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\map') {
                $node->name = new Node\Name\FullyQualified('Tempest\Mapper\map');

                return null;
            }

            if ($functionName === 'Tempest\make') {
                $node->name = new Node\Name\FullyQualified('Tempest\Mapper\make');

                return null;
            }
        }

        return null;
    }
}
