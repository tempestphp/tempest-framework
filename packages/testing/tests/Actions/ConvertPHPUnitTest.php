<?php

namespace Tempest\Testing\Tests\Actions;

use Tempest\Testing\Actions\ConvertPHPUnit;
use Tempest\Testing\Test;

use function Tempest\Testing\test;

final class ConvertPHPUnitTest
{
    #[Test]
    public function convertPHPUnitTest(ConvertPHPUnit $convert): void
    {
        $input = file_get_contents(__DIR__ . '/../Fixtures/phpunit_test.stub.php');

        test($convert($input))
            ->contains('use function Tempest\Testing\test;')
            ->contains('use Tempest\Testing\Test;')
            ->containsNot('extends FrameworkIntegrationTestCase')
            ->containsNot('$this->assertSame(\'baz\', $foo->bar);')
            ->contains('test($foo->bar)->is(\'baz\');')
            ->containsNot('$this->assertInstanceOf(PrimaryKey::class, $foo->id);')
            ->contains('test($foo->id)->instanceOf(PrimaryKey::class);');
    }
}
