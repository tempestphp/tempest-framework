<?php

declare(strict_types=1);

namespace Tempest\Support\Tests;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Support\LanguageHelper;

/**
 * @internal
 * @small
 */
final class LanguageHelperTest extends TestCase
{
    #[TestWith([['Jon', 'Jane'], 'Jon and Jane'])]
    #[TestWith([['Jon', 'Jane', 'Jill'], 'Jon, Jane and Jill'])]
    public function test_join(array $parts, string $expected): void
    {
        $this->assertEquals($expected, LanguageHelper::join($parts));
    }
}
