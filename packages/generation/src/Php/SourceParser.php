<?php

declare(strict_types=1);

namespace Tempest\Generation\Php;

use PhpParser\Error;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpParser\Token;
use Tempest\Generation\Php\Exceptions\PhpCodeWasInvalid;

/**
 * @internal
 */
final class SourceParser
{
    private static ?Parser $parser = null;

    /**
     * @return array{Stmt[], Token[]}
     */
    public static function parse(string $code): array
    {
        self::$parser ??= new ParserFactory()->createForVersion(PhpVersion::fromComponents(8, 5));

        try {
            $stmts = self::$parser->parse($code) ?? [];
        } catch (Error $error) {
            throw new PhpCodeWasInvalid($error);
        }

        return [$stmts, self::$parser->getTokens()];
    }

    public static function parseExpression(string $code): Expr
    {
        [$stmts] = self::parse("<?php {$code};");

        if (count($stmts) !== 1 || ! $stmts[0] instanceof Stmt\Expression) {
            throw new PhpCodeWasInvalid(new Error(sprintf('"%s" is not a single expression', $code)));
        }

        return $stmts[0]->expr;
    }
}
