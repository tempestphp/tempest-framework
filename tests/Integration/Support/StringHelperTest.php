<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use Tempest\Support\StringHelper;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class StringHelperTest extends FrameworkIntegrationTestCase
{
    public function test_plural_studly(): void
    {
        $this->assertSame('RealHumans', StringHelper::pluralizeLast('RealHuman'));
        $this->assertSame('Models', StringHelper::pluralizeLast('Model'));
        $this->assertSame('VortexFields', StringHelper::pluralizeLast('VortexField'));
        $this->assertSame('MultipleWordsInOneStrings', StringHelper::pluralizeLast('MultipleWordsInOneString'));
    }
}
