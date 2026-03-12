<?php

namespace Tempest\Upgrade\Tempest2;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Rector\AbstractRector;

final class MigrationRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?int
    {
        if (! $node instanceof Class_) {
            return null;
        }

        // Check whether this class implements Tempest\Database\DatabaseMigration
        $implements = $node->implements;

        $implementsDatabaseMigration = array_find_key(
            $implements,
            static fn (Name $name) => $name->toString() === 'Tempest\Database\DatabaseMigration',
        );

        if ($implementsDatabaseMigration === null) {
            return null;
        }

        // Unset the old interface
        unset($implements[$implementsDatabaseMigration]);

        // Add the new MigrateUp interface
        $implements[] = new Name('\Tempest\Database\MigratesUp');
        $node->getMethod('up')->returnType = new Name('QueryStatement');

        // Check whether the migration has a down method implemented or not
        $downStatements = $node->getMethod('down')->stmts;

        $migratesDown = true;

        foreach ($downStatements as $statement) {
            if (! $statement instanceof Return_) {
                continue;
            }

            if (! $statement->expr instanceof ConstFetch) {
                continue;
            }

            $migratesDown = $statement->expr->name->toString() !== 'null';

            break;
        }

        if ($migratesDown) {
            // If the migration has a down method implemented, we'll add the new MigrateDown interface
            $implements[] = new Name('\Tempest\Database\MigratesDown');
            $node->getMethod('down')->returnType = new Name('QueryStatement');
        } else {
            // If the migration does not have a down method implemented, we'll remove it entirely
            $statements = $node->stmts;

            foreach ($node->stmts as $key => $statement) {
                if (! $statement instanceof ClassMethod) {
                    continue;
                }

                if ($statement->name->toString() !== 'down') {
                    continue;
                }

                unset($statements[$key]);

                $node->stmts = $statements;
            }
        }

        $node->implements = $implements;

        return null;
    }
}
