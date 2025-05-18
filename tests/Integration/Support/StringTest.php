<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\str;

/**
 * @internal
 */
final class StringTest extends FrameworkIntegrationTestCase
{
    public function test_plural_studly(): void
    {
        $this->assertTrue(str('RealHuman')->pluralizeLastWord()->equals('RealHumans'));
        $this->assertTrue(str('Model')->pluralizeLastWord()->equals('Models'));
        $this->assertTrue(str('VortexField')->pluralizeLastWord()->equals('VortexFields'));
        $this->assertTrue(str('MultipleWordsInOneString')->pluralizeLastWord()->equals('MultipleWordsInOneStrings'));
    }

    public function test_singularize(): void
    {
        $this->assertTrue(str('RealHumans')->singularizeLastWord()->equals('RealHuman'));
        $this->assertTrue(str('Models')->singularizeLastWord()->equals('Model'));
        $this->assertTrue(str('VortexFields')->singularizeLastWord()->equals('VortexField'));
        $this->assertTrue(str('MultipleWordsInOneStrings')->singularizeLastWord()->equals('MultipleWordsInOneString'));
    }
}
