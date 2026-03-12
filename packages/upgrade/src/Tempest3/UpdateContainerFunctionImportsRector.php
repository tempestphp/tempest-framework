<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\UseItem;
use Rector\Rector\AbstractRector;

final class UpdateContainerFunctionImportsRector extends AbstractRector
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
            if ($node->name->toString() === 'Tempest\get') {
                $node->name = new Name('Tempest\Container\get');
            }

            if ($node->name->toString() === 'Tempest\invoke') {
                $node->name = new Name('Tempest\Container\invoke');
            }

            return null;
        }

        if ($node instanceof FuncCall && $node->name instanceof Name) {
            $functionName = $node->name->toString();

            if ($functionName === 'Tempest\get') {
                $node->name = new FullyQualified('Tempest\Container\get');

                return null;
            }

            if ($functionName === 'Tempest\invoke') {
                $node->name = new FullyQualified('Tempest\Container\invoke');

                return null;
            }
        }

        return null;
    }
}
