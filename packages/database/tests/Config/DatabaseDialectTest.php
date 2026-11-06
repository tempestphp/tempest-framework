<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Config;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;

/**
 * @internal
 */
final class DatabaseDialectTest extends TestCase
{
    #[TestWith([DatabaseDialect::MYSQL, 'title', '`title`'])]
    #[TestWith([DatabaseDialect::MYSQL, 'ti`tle', '`ti``tle`'])]
    #[TestWith([DatabaseDialect::SQLITE, 'title', '`title`'])]
    #[TestWith([DatabaseDialect::SQLITE, 'ti`tle', '`ti``tle`'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, 'title', '"title"'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, 'ti"tle', '"ti""tle"'])]
    #[Test]
    public function quote_identifier(DatabaseDialect $dialect, string $identifier, string $expected): void
    {
        $this->assertSame($expected, $dialect->quoteIdentifier($identifier));
    }
}
