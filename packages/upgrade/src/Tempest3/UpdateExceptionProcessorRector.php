<?php

namespace Tempest\Upgrade\Tempest3;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;

final class UpdateExceptionProcessorRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Node\UseItem::class,
            Node\Stmt\Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if ($node instanceof Node\UseItem) {
            $name = $node->name->toString();

            if ($name === 'Tempest\Core\ExceptionProcessor' || $name === 'ExceptionProcessor') {
                $node->name = new Node\Name('Tempest\Core\Exceptions\ExceptionReporter');
            }

            return null;
        }

        if (! $node instanceof Node\Stmt\Class_) {
            return null;
        }

        $implements = $node->implements;

        $implementsExceptionProcessor = array_find_key(
            array: $implements,
            callback: static fn (Node\Name $name) => $name->toString() === 'Tempest\Core\ExceptionProcessor' || $name->toString() === 'ExceptionProcessor',
        );

        if ($implementsExceptionProcessor === null) {
            return null;
        }

        $implements[$implementsExceptionProcessor] = new Node\Name('\Tempest\Core\Exceptions\ExceptionReporter');
        $node->implements = $implements;

        foreach ($node->stmts as $statement) {
            if (! $statement instanceof ClassMethod) {
                continue;
            }

            if ($statement->name->toString() === 'process') {
                $statement->name = new Node\Identifier('report');
                break;
            }
        }

        return null;
    }
}
